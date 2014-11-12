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
    Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
// JSON-API.
use GoIntegro\Bundle\HateoasBundle\JsonApi\Exception\DocumentTooLargeHttpException;
// Utils.
use GoIntegro\Bundle\HateoasBundle\Util\Inflector;
// Security.
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
// Validator.
use Symfony\Component\Validator\Exception\ValidatorException;

/**
 * Permite probar la flexibilidad de la biblioteca.
 * @todo Refactor.
 */
class MagicController extends SymfonyController
{
    use CommonResponseTrait;

    const RESOURCE_LIMIT = 50,
        ERROR_RESOURCE_NOT_FOUND = "The resource was not found.",
        ERROR_RELATIONSHIP_NOT_FOUND = "No relationship by that name found.",
        ERROR_FIELD_NOT_FOUND = "No field by that name found.";

    /**
     * @Route("/{primaryType}/{id}/linked/{relationship}", name="hateoas_magic_relation", methods="GET")
     * @param string $primaryType
     * @param string $id
     * @param string $relationship
     * @throws HttpException
     * @throws NotFoundHttpException
     * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html#sec10.4.14
     */
    public function getRelationAction($primaryType, $id, $relationship)
    {
        $params = $this->get('hateoas.request_parser')->parse();

        if (empty($params->primaryClass)) {
            throw new NotFoundHttpException(self::ERROR_RESOURCE_NOT_FOUND);
        }

        $entity = $this->getDoctrine()
            ->getManager()
            ->find($params->primaryClass, $id);

        if (empty($entity)) {
            throw new NotFoundHttpException(self::ERROR_RESOURCE_NOT_FOUND);
        }

        // @todo Intentar evitar crear el recurso. Necesitamos poder manejar los blacklists desde la metadata, o algo así.
        $primaryResource = $this->get('hateoas.resource_manager')
            ->createResourceFactory()
            ->setEntity($entity)
            ->create();
        $metadata = $primaryResource->getMetadata();
        $json = NULL;
        $relation = NULL;
        $relatedResource = NULL;

        if ($metadata->isRelationship($relationship)) {
            $relation = $primaryResource->callGetter($relationship);
        } else {
            throw new NotFoundHttpException(
                self::ERROR_RELATIONSHIP_NOT_FOUND
            );
        }

        if ($metadata->isToManyRelationship($relationship)) {
            if ($relation instanceof Collection) {
                $relation = $relation->toArray();
            }

            if (Controller::DEFAULT_RESOURCE_LIMIT < count($relation)) {
                throw new DocumentTooLargeHttpException;
            }

            $relatedResource = $this->get('hateoas.resource_manager')
                ->createCollectionFactory()
                ->addEntities($relation)
                ->create();
        } elseif ($metadata->isToOneRelationship($relationship)) {
            $relatedResource = empty($relation)
                ? NULL
                : $this->get('hateoas.resource_manager')
                    ->createResourceFactory()
                    ->setEntity($relation)
                    ->create();
        } else {
            throw new NotFoundHttpException(
                self::ERROR_RELATIONSHIP_NOT_FOUND
            );
        }

        $json = empty($relatedResource)
            ? NULL
            : $this->get('hateoas.resource_manager')
                ->createSerializerFactory()
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
        $params = $this->get('hateoas.request_parser')->parse();

        if (empty($params->primaryClass)) {
            throw new NotFoundHttpException(self::ERROR_RESOURCE_NOT_FOUND);
        }

        $entity = $this->getDoctrine()
            ->getManager()
            ->find($params->primaryClass, $id);

        if (empty($entity)) {
            throw new NotFoundHttpException(self::ERROR_RESOURCE_NOT_FOUND);
        }

        // @todo Intentar evitar crear el recurso. Necesitamos poder manejar los blacklists desde la metadata, o algo así.
        $resource = $this->get('hateoas.resource_manager')
            ->createResourceFactory()
            ->setEntity($entity)
            ->create();
        $metadata = $resource->getMetadata();
        $json = NULL;

        if ($metadata->isField($field)) {
            $json = $resource->callGetter($field);
        } else {
            throw new NotFoundHttpException(self::ERROR_FIELD_NOT_FOUND);
        }

        return $this->createETagResponse($json);
    }

    /**
     * @Route("/{primaryType}/{id}", name="hateoas_magic_one", methods="GET")
     * @param string $primaryType
     * @param string $id
     * @throws NotFoundHttpException
     */
    public function getOneAction($primaryType, $id)
    {
        $ids = explode(',', $id);
        $params = $this->get('hateoas.request_parser')->parse();

        if (empty($params->primaryClass)) {
            throw new NotFoundHttpException(self::ERROR_RESOURCE_NOT_FOUND);
        }

        $entities = $this->getDoctrine()
            ->getManager()
            ->getRepository($params->primaryClass)
            ->findById($ids);

        if (empty($entities) || count($entities) !== count($ids)) {
            throw new NotFoundHttpException(self::ERROR_RESOURCE_NOT_FOUND);
        }

        $resource = 1 < count($entities)
            ? $this->get('hateoas.resource_manager')
                ->createCollectionFactory()
                ->addEntities($entities)
                ->create()
            : $this->get('hateoas.resource_manager')
                ->createResourceFactory()
                ->setEntity(current($entities))
                ->create();

        $json = $this->get('hateoas.resource_manager')
            ->createSerializerFactory()
            ->setDocumentResources($resource)
            ->create()
            ->serialize();

        return $this->createETagResponse($json);
    }

    /**
     * @Route("/{primaryType}", name="hateoas_magic_all", methods="GET")
     * @param string $primaryType
     * @throws HttpException
     * @throws NotFoundHttpException
     * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html#sec10.4.14
     */
    public function getAllAction($primaryType)
    {
        $params = $this->get('hateoas.request_parser')->parse();

        if (empty($params->primaryClass)) {
            throw new NotFoundHttpException(self::ERROR_RESOURCE_NOT_FOUND);
        }

        $resources = NULL;
        $params = $this->get('hateoas.request_parser')->parse();
        $entities
            = $this->get('hateoas.repo_helper')->findByRequestParams($params);

        if (Controller::DEFAULT_RESOURCE_LIMIT < count($entities)) {
            throw new DocumentTooLargeHttpException;
        }

        $resources = $this->get('hateoas.resource_manager')
            ->createCollectionFactory()
            ->setPaginator($entities->getPaginator())
            ->create();
        $json = $this->get('hateoas.resource_manager')
            ->createSerializerFactory()
            ->setDocumentResources($resources)
            ->create()
            ->serialize();

        return $this->createETagResponse($json);
    }

    /**
     * @Route("/{primaryType}", name="hateoas_magic_create", methods="POST")
     * @param string $primaryType
     */
    public function createAction($primaryType)
    {
        $rawBody = $this->getRequest()->getContent();

        $params = $this->get('hateoas.request_parser')->parse();
        $raml = $this->get('hateoas.raml.finder')->find($params->primaryType);

        if (!$this->get('hateoas.json_coder')->matchSchema($rawBody, $raml)) {
            $message = $this->get('hateoas.json_coder')
                ->getSchemaErrorMessage();
            throw new BadRequestHttpException($message);
        }

        $data = $this->get('hateoas.json_coder')->decode($rawBody);

        try {
            $entity = $this->get('hateoas.entity.builder')->create($data);
        } catch (AccessDeniedException $e) {
            throw new AccessDeniedHttpException($e->getMessage(), $e);
        } catch (ValidatorException $e) {
            throw new BadRequestHttpException($e->getMessage(), $e);
        }

        $resource = $this->get('hateoas.resource_manager')
            ->createResourceFactory()
            ->setEntity($entity)
            ->create();
        $json = $this->get('hateoas.resource_manager')
            ->createSerializerFactory()
            ->setDocumentResources($resource)
            ->create()
            ->serialize();

        return $this->createETagResponse($json, Response::HTTP_CREATED);
    }

    /**
     * @Route("/{primaryType}/{id}", name="hateoas_magic_update", methods="PUT")
     * @param string $primaryType
     * @param string $id
     * @throws NotFoundHttpException
     * @todo Finish.
     */
    public function updateAction($primaryType, $id)
    {
        $ids = explode(',', $id);
        $params = $this->get('hateoas.request_parser')->parse();

        if (empty($params->primaryClass)) {
            throw new NotFoundHttpException(self::ERROR_RESOURCE_NOT_FOUND);
        }

        $entities = $this->getDoctrine()
            ->getManager()
            ->getRepository($params->primaryClass)
            ->findById($ids);

        if (empty($entities) || count($entities) !== count($ids)) {
            throw new NotFoundHttpException(self::ERROR_RESOURCE_NOT_FOUND);
        }

        // @todo Terminar.
        $post = array_pop($entities);

        if (!$this->get('security.context')->isGranted('edit', $post)) {
            throw new AccessDeniedHttpException('Unauthorized access!');
        }

        $rawBody = $this->getRequest()->getContent();
        $raml = $this->get('hateoas.raml.finder')->find($params->primaryType);

        if (!$this->get('hateoas.json_coder')->matchSchema($rawBody, $raml)) {
            $message = $this->get('hateoas.json_coder')
                ->getSchemaErrorMessage();
            throw new BadRequestHttpException($message);
        }

        $data = $this->get('hateoas.json_coder')->decode($rawBody);
        $post->setContent($data['posts']['content']);
        $errors = $this->get('validator')->validate($post);

        if (0 < count($errors)) {
            throw new BadRequestHttpException($errors);
        }

        $em = $this->get('doctrine.orm.entity_manager');
        $em->persist($post);
        $em->flush();

        $resource = $this->get('hateoas.resource_manager')
            ->createResourceFactory()
            ->setEntity($post)
            ->create();
        $json = $this->get('hateoas.resource_manager')
            ->createSerializerFactory()
            ->setDocumentResources($resource)
            ->create()
            ->serialize();

        return $this->createETagResponse($json);
    }
}
