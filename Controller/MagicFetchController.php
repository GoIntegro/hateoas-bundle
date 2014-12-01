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
    Symfony\Component\HttpKernel\Exception\BadRequestHttpException,
    Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException,
    Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException,
    GoIntegro\Bundle\HateoasBundle\Http\DocumentTooLargeException;
// JSON-API.
use GoIntegro\Bundle\HateoasBundle\JsonApi\Exception\DocumentTooLargeHttpException,
    GoIntegro\Bundle\HateoasBundle\JsonApi\ResourceEntityInterface,
    GoIntegro\Bundle\HateoasBundle\JsonApi\Request\Params,
    GoIntegro\Bundle\HateoasBundle\JsonApi\Document,
    GoIntegro\Bundle\HateoasBundle\JsonApi\Exception\NotFoundException;
// Utils.
use GoIntegro\Bundle\HateoasBundle\Util\Inflector;
// Security.
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
// Request.
use GoIntegro\Bundle\HateoasBundle\JsonApi\Request\ParseException,
    GoIntegro\Bundle\HateoasBundle\JsonApi\Request\ActionNotAllowedException,
    GoIntegro\Bundle\HateoasBundle\JsonApi\Request\EntityAccessDeniedException,
    GoIntegro\Bundle\HateoasBundle\JsonApi\Request\RequestAction;

class MagicFetchController extends SymfonyController
{
    use CommonResponseTrait;

    const ERROR_ACCESS_DENIED = "Access to the resource was denied.",
        ERROR_RESOURCE_NOT_FOUND = "The resource was not found.",
        ERROR_RELATIONSHIP_NOT_FOUND = "No relationship by that name found.",
        ERROR_FIELD_NOT_FOUND = "No field by that name found.";

    /**
     * @Route("/{primaryType}/{id}/links/{relationship}", name="hateoas_magic_relation", methods="GET")
     * @param string $primaryType
     * @param string $id
     * @param string $relationship
     * @throws HttpException
     * @throws NotFoundHttpException
     * @see http://jsonapi.org/format/#urls-relationships
     * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html#sec10.4.14
     */
    public function getRelationAction($primaryType, $id, $relationship)
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
        }

        $relatedResource = NULL;

        if (RequestAction::TYPE_MULTIPLE == $params->action->type) {
            $relatedResource = $this->get('hateoas.resource_manager')
                ->createCollectionFactory()
                ->setParams($params)
                ->addEntities($params->entities->relationship)
                ->create();
        } elseif (!empty($params->entities->relationship)) {
            $relatedResource = $this->get('hateoas.resource_manager')
                ->createResourceFactory()
                ->setEntity(reset($params->entities->relationship))
                ->create();
        }

        $json = empty($relatedResource)
            ? NULL
            : $this->get('hateoas.resource_manager')
                ->createSerializerFactory()
                ->setParams($params)
                ->setDocumentResources($relatedResource)
                ->create()
                ->serialize();

        return $this->createETagResponse($json);
    }

    /**
     * @Route("/{primaryType}/{id}/{field}", name="hateoas_magic_field", methods="GET")
     * @param string $primaryType
     * @param string $id
     * @param string $relationship
     * @throws HttpException
     * @throws NotFoundHttpException
     * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html#sec10.4.14
     */
    public function getFieldAction($primaryType, $id, $field)
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
        }

        $metadata = $this->get('hateoas.metadata_miner')
            ->mine($params->primaryClass);
        $json = NULL;

        if ($metadata->isField($field)) {
            $entity = reset($params->entities->primary);
            $resource = $this->get('hateoas.resource_manager')
                ->createResourceFactory()
                ->setEntity($entity)
                ->create();
            $json = $resource->callGetter($field);
        } else {
            throw new NotFoundHttpException(self::ERROR_FIELD_NOT_FOUND);
        }

        return $this->createETagResponse($json);
    }

    /**
     * @Route("/{primaryType}/{ids}", name="hateoas_magic_one", methods="GET")
     * @param string $primaryType
     * @param string $ids
     */
    public function getByIdsAction($primaryType, $ids)
    {
        try {
            $params = $this->get('hateoas.request_parser')->parse($this->getRequest());
        } catch (NotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        } catch (ActionNotAllowedException $e) {
            throw new MethodNotAllowedHttpException(
                $e->getAllowedMethods(), $e->getMessage(), $e
            );
        } catch (EntityAccessDeniedException $e) {
            throw new AccessDeniedHttpException($e->getMessage(), $e);
        } catch (DocumentTooLargeException $e) {
            throw new DocumentTooLargeHttpException($e->getMessage(), $e);
        }

        $resources = 1 < count($params->entities->primary)
            ? $this->get('hateoas.resource_manager')
                ->createCollectionFactory()
                ->setParams($params)
                ->addEntities($params->entities->primary)
                ->create()
            : $this->get('hateoas.resource_manager')
                ->createResourceFactory()
                ->setEntity(reset($params->entities->primary))
                ->create();

        $factory = $this->get('hateoas.resource_manager')
            ->createSerializerFactory()
            ->setParams($params)
            ->setDocumentResources($resources);

        if (!empty($params->entities->translations)) {
            $factory->addMeta([
                'translations' => $params->entities->translations
            ]);
        }

        $json = $factory->create()->serialize();

        return $this->createETagResponse($json);
    }

    /**
     * @Route("/{primaryType}", name="hateoas_magic_all", methods="GET")
     * @param string $primaryType
     * @throws NotFoundHttpException
     * @throws DocumentTooLargeHttpException
     * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html#sec10.4.14
     */
    public function getWithFiltersAction($primaryType)
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
        }

        $resources = NULL;
        $params = $this->get('hateoas.request_parser')->parse($this->getRequest());
        $filter = function(ResourceEntityInterface $entity) {
            return $this->get('security.context')->isGranted('view', $entity);
        };
        $entities = $this->get('hateoas.repo_helper')
            ->findByRequestParams($params)
            ->filter($filter);

        if (Document::DEFAULT_RESOURCE_LIMIT < count($entities)) {
            throw new DocumentTooLargeHttpException;
        }

        $resources = 0 === count($entities)
            ? $this->get('hateoas.resource_manager')
                ->createCollectionFactory()
                ->setParams($params)
                ->addEntities($entities->toArray())
                ->create()
            : $this->get('hateoas.resource_manager')
                ->createCollectionFactory()
                ->setPaginator($entities->getPaginator())
                ->create();
        $json = $this->get('hateoas.resource_manager')
            ->createSerializerFactory()
            ->setParams($params)
            ->setDocumentResources($resources)
            ->create()
            ->serialize();

        return $this->createETagResponse($json);
    }
}
