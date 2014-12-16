<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\Controller;

// Controladores.
use Symfony\Bundle\FrameworkBundle\Controller\Controller as SymfonyController,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Route,
    Symfony\Component\HttpFoundation\JsonResponse;
// Colecciones.
use Doctrine\Common\Collections\Collection;
// HTTP.
use Symfony\Component\HttpFoundation\Response,
    Symfony\Component\HttpKernel\Exception\NotFoundHttpException,
    Symfony\Component\HttpKernel\Exception\ConflictHttpException,
    Symfony\Component\HttpKernel\Exception\BadRequestHttpException,
    Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException,
    Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException,
    GoIntegro\Bundle\HateoasBundle\Http\DocumentTooLargeException;
// JSON-API.
use GoIntegro\Bundle\HateoasBundle\JsonApi\Exception\DocumentTooLargeHttpException,
    GoIntegro\Bundle\HateoasBundle\JsonApi\ResourceEntityInterface,
    GoIntegro\Bundle\HateoasBundle\JsonApi\Request\Params,
    GoIntegro\Bundle\HateoasBundle\JsonApi\Document,
    GoIntegro\Bundle\HateoasBundle\JsonApi\Exception\ConflictException,
    GoIntegro\Bundle\HateoasBundle\JsonApi\Exception\NotFoundException;
// Utils.
use GoIntegro\Bundle\HateoasBundle\Util\Inflector;
// Security.
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
// Validator.
use GoIntegro\Bundle\HateoasBundle\Entity\Validation\EntityConflictExceptionInterface,
    GoIntegro\Bundle\HateoasBundle\Entity\Validation\ValidationExceptionInterface;
// Request.
use GoIntegro\Bundle\HateoasBundle\JsonApi\Request\ParseException,
    GoIntegro\Bundle\HateoasBundle\JsonApi\Request\ActionNotAllowedException,
    GoIntegro\Bundle\HateoasBundle\JsonApi\Request\EntityAccessDeniedException;

class MagicAlterController extends SymfonyController
{
    use CommonResponseTrait;

    const ERROR_ACCESS_DENIED = "Access to the resource was denied.",
        ERROR_RESOURCE_NOT_FOUND = "The resource was not found.",
        ERROR_RELATIONSHIP_NOT_FOUND = "No relationship by that name found.",
        ERROR_FIELD_NOT_FOUND = "No field by that name found.";

    /**
     * @Route("/{primaryType}/{id}/links/{relationship}", name="hateoas_magic_link", methods="POST")
     * @Route("/{primaryType}/{id}/links/{relationship}", name="hateoas_magic_update_link", methods="PUT")
     * @Route("/{primaryType}/{id}/links/{relationship}", name="hateoas_magic_unlink_one", methods="DELETE")
     * @Route("/{primaryType}/{id}/links/{relationship}/{ids}", name="hateoas_magic_unlink_many", methods="DELETE")
     * @param string $primaryType
     * @param string $id
     * @param string $relationship
     * @param string $ids
     * @throws AccessDeniedHttpException
     * @throws NotFoundHttpException
     * @throws BadRequestHttpException
     * @see http://jsonapi.org/format/#crud-updating-relationships
     * @todo The 409 response should happend during parsing.
     */
    public function linkAction($primaryType, $id, $relationship, $ids = NULL)
    {
        try {
            $params = $this->get('hateoas.request_parser')->parse($this->getRequest());
        } catch (NotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        } catch (ActionNotAllowedException $e) {
            throw new MethodNotAllowedHttpException(
                $e->getAllowedMethods(), $e->getMessage(), $e
            );
        } catch (ParseException $e) {
            throw new BadRequestHttpException($e->getMessage(), $e);
        } catch (EntityAccessDeniedException $e) {
            throw new AccessDeniedHttpException($e->getMessage(), $e);
        } catch (ConflictException $e) {
            throw new ConflictHttpException($e->getMessage(), $e);
        }

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        try {
            foreach ($params->entities->primary as &$entity) {
                $data = $params->resources[$entity->getId()];
                $links = $this->extractLinks($data);
                $metadata = $this->extractMetadata($data);

                try {
                    $entity = $this->get('hateoas.entity.mutator')
                        ->update($params, $entity, $data, $links, $metadata);
                } catch (EntityConflictExceptionInterface $e) {
                    throw new ConflictHttpException($e->getMessage(), $e);
                } catch (ValidationExceptionInterface $e) {
                    throw new BadRequestHttpException($e->getMessage(), $e);
                }
            }

            $em->getConnection()->commit();
        } catch (\Exception $e) {
            $em->getConnection()->rollback();
            throw $e;
        }

        return $this->createNoCacheResponse(NULL, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/{primaryType}", name="hateoas_magic_create", methods="POST")
     * @param string $primaryType
     * @throws AccessDeniedHttpException
     * @throws NotFoundHttpException
     * @throws BadRequestHttpException
     * @see http://jsonapi.org/format/#crud-creating-resources
     * @todo Support multi-create.
     * @todo Rollback everything if anything goes wrong.
     */
    public function createAction($primaryType)
    {
        try {
            $params = $this->get('hateoas.request_parser')->parse($this->getRequest());
        } catch (NotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        } catch (ActionNotAllowedException $e) {
            throw new MethodNotAllowedHttpException(
                $e->getAllowedMethods(), $e->getMessage(), $e
            );
        } catch (ParseException $e) {
            throw new BadRequestHttpException($e->getMessage(), $e);
        } catch (EntityAccessDeniedException $e) {
            throw new AccessDeniedHttpException($e->getMessage(), $e);
        } catch (DocumentTooLargeException $e) {
            throw new DocumentTooLargeHttpException($e->getMessage(), $e);
        } catch (ConflictException $e) {
            throw new ConflictHttpException($e->getMessage(), $e);
        }

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        try {
            $entities = [];

            foreach ($params->resources as $data) {
                try {
                    $links = $this->extractLinks($data);
                    $metadata = $this->extractMetadata($data);
                    $entities[] = $this->get('hateoas.entity.builder')
                        ->create($params, $data, $links, $metadata);
                } catch (EntityConflictExceptionInterface $e) {
                    throw new ConflictHttpException($e->getMessage(), $e);
                } catch (ValidationExceptionInterface $e) {
                    throw new BadRequestHttpException($e->getMessage(), $e);
                }
            }

            $em->getConnection()->commit();
        } catch (\Exception $e) {
            $em->getConnection()->rollback();
            throw $e;
        }

        $resources = 1 < count($entities)
            ? $this->get('hateoas.resource_manager')
                ->createCollectionFactory()
                ->setParams($params)
                ->addEntities($entities)
                ->create()
            : $this->get('hateoas.resource_manager')
                ->createResourceFactory()
                ->setEntity(reset($entities))
                ->create();

        $document = $this->get('hateoas.resource_manager')
            ->createDocumentFactory()
            ->setParams($params)
            ->setResources($resources)
            ->create();

        $json = $this->get('hateoas.serializer.document')
            ->serialize($document);

        return $this->createNoCacheResponse($json, Response::HTTP_CREATED);
    }

    /**
     * @Route("/{primaryType}/{ids}", name="hateoas_magic_update", methods="PUT")
     * @param string $primaryType
     * @param string $ids
     * @throws AccessDeniedHttpException
     * @throws NotFoundHttpException
     * @throws BadRequestHttpException
     * @see http://jsonapi.org/format/#crud-updating
     * @todo Rollback everything if anything goes wrong.
     */
    public function updateAction($primaryType, $ids)
    {
        try {
            $params = $this->get('hateoas.request_parser')->parse($this->getRequest());
        } catch (NotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        } catch (ActionNotAllowedException $e) {
            throw new MethodNotAllowedHttpException(
                $e->getAllowedMethods(), $e->getMessage(), $e
            );
        } catch (ParseException $e) {
            throw new BadRequestHttpException($e->getMessage(), $e);
        } catch (EntityAccessDeniedException $e) {
            throw new AccessDeniedHttpException($e->getMessage(), $e);
        } catch (DocumentTooLargeException $e) {
            throw new DocumentTooLargeHttpException($e->getMessage(), $e);
        } catch (ConflictException $e) {
            throw new ConflictHttpException($e->getMessage(), $e);
        }

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        try {
            foreach ($params->entities->primary as &$entity) {
                $data = $params->resources[$entity->getId()];
                $links = $this->extractLinks($data);
                $metadata = $this->extractMetadata($data);

                try {
                    $entity = $this->get('hateoas.entity.mutator')
                        ->update($params, $entity, $data, $links, $metadata);
                } catch (EntityConflictExceptionInterface $e) {
                    throw new ConflictHttpException($e->getMessage(), $e);
                } catch (ValidationExceptionInterface $e) {
                    throw new BadRequestHttpException($e->getMessage(), $e);
                }
            }

            $em->getConnection()->commit();
        } catch (\Exception $e) {
            $em->getConnection()->rollback();
            throw $e;
        }

        $resources = 1 < count($params->entities->primary)
            ? $this->get('hateoas.resource_manager')
                ->createCollectionFactory()
                ->setParams($params)
                ->addEntities($params->entities->primary->toArray())
                ->create()
            : $this->get('hateoas.resource_manager')
                ->createResourceFactory()
                ->setEntity($params->entities->primary->first())
                ->create();

        $document = $this->get('hateoas.resource_manager')
            ->createDocumentFactory()
            ->setParams($params)
            ->setResources($resources)
            ->create();

        $json = $this->get('hateoas.serializer.document')
            ->serialize($document);

        return $this->createNoCacheResponse($json);
    }

    /**
     * @Route("/{primaryType}/{ids}", name="hateoas_magic_delete", methods="DELETE")
     * @param string $primaryType
     * @param string $ids
     * @throws AccessDeniedHttpException
     * @throws NotFoundHttpException
     * @throws BadRequestHttpException
     * @see http://jsonapi.org/format/#crud-deleting
     * @todo Rollback everything if anything goes wrong.
     */
    public function deleteAction($primaryType, $ids)
    {
        try {
            $params = $this->get('hateoas.request_parser')->parse($this->getRequest());
        } catch (NotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        } catch (ActionNotAllowedException $e) {
            throw new MethodNotAllowedHttpException(
                $e->getAllowedMethods(), $e->getMessage(), $e
            );
        } catch (ParseException $e) {
            throw new BadRequestHttpException($e->getMessage(), $e);
        } catch (EntityAccessDeniedException $e) {
            throw new AccessDeniedHttpException($e->getMessage(), $e);
        } catch (DocumentTooLargeException $e) {
            throw new DocumentTooLargeHttpException($e->getMessage(), $e);
        } catch (ConflictException $e) {
            throw new ConflictHttpException($e->getMessage(), $e);
        }

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        try {
            foreach ($params->entities->primary as $entity) {
                $this->get('hateoas.entity.deleter')
                    ->delete($params, $entity);
            }

            $em->getConnection()->commit();
        } catch (\Exception $e) {
            $em->getConnection()->rollback();
            throw $e;
        }

        return $this->createNoCacheResponse(NULL, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param array &$data
     * @return array
     * @todo Move.
     */
    private function extractLinks(array &$data)
    {
        $links = isset($data['links']) ? $data['links'] : [];
        unset($data['links']);

        return $links;
    }

    /**
     * @param array &$data
     * @return array
     * @todo Move.
     */
    private function extractMetadata(array &$data)
    {
        $metadata = isset($data['meta']) ? $data['meta'] : [];
        unset($data['meta']);

        return $metadata;
    }
}
