<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\JsonApi\Serializer;

// Collections.
use Doctrine\Common\Collections\Collection as CollectionInterface;
// Inflection.
use GoIntegro\Bundle\HateoasBundle\Util\Inflector;
// JSON-API.
use GoIntegro\Bundle\HateoasBundle\JsonApi\DocumentResource,
    GoIntegro\Bundle\HateoasBundle\JsonApi\EntityResource;
// Security.
use Symfony\Component\Security\Core\SecurityContextInterface;

class ResourceObjectSerializer implements SerializerInterface
{
    const ACCESS_VIEW = 'view';

    const ERROR_FIELD_IS_RELATIONSHIP = "El campo \"%s\" es en sí un recurso vinculado.",
        ERROR_UNKOWN_FIELD = "El campo \"%s\" no existe.";

    public $resource;
    public $fields = [];
    /**
     * @var SecurityContextInterface
     */
    private $securityContext;

    /**
     * @param DocumentResource $resource
     * @param SecurityContextInterface $securityContext
     * @param array $sparseFields
     */
    public function __construct(
        DocumentResource $resource,
        SecurityContextInterface $securityContext,
        array $sparseFields = []
    )
    {
        $this->resource = $resource;
        $this->securityContext = $securityContext;
        $this->fields = $sparseFields ?: $resource->getMetadata()->fields;
    }

    public function serialize()
    {
        $metadata = $this->resource->getMetadata();
        $json = [
            'id' => $this->resource->id,
            'type' => $metadata->type
        ];

        if ($this->resource->getMetadata()->type != $metadata->subtype) {
            $json['subtype'] = $metadata->subtype;
        }

        foreach ($this->fields as $field) {
            if ($metadata->isRelationship($field)) {
                $message = sprintf(self::ERROR_FIELD_IS_RELATIONSHIP, $field);
                throw new InvalidFieldException($message);
            }

            if ($this->resource->isFieldBlacklisted($field)) {
                $message = sprintf(self::ERROR_UNKOWN_FIELD, $field);
                throw new InvalidFieldException($message);
            }

            try {
                $value = $this->resource->callGetter($field);
            } catch (\Exception $e) {
                $message = sprintf(self::ERROR_UNKOWN_FIELD, $field);
                throw new InvalidFieldException($message);
            }

            if ('object' == gettype($value)) {
                $value = $this->serializeObjectFieldValue($value);
            }

            $json[$field] = $value;
        }

        if ($metadata->hasRelationships()) {
            $json['links']
                = $this->getResourceLinks($this->resource);
        }

        return $json;
    }

    /**
     * @param object $fieldValue
     */
    private function serializeObjectFieldValue($fieldValue)
    {
        $serializer = new ObjectFieldValueSerializer($fieldValue);

        return $serializer->serialize();
    }

    private function getResourceLinks(DocumentResource $resource)
    {
        $links = [];

        foreach (
            $this->resource->getMetadata()->relationships->toOne
                as $relationship => $relation
        ) {
            $entity = $this->resource->callGetter($relationship);

            if (!$this->securityContext->isGranted(
                static::ACCESS_VIEW, $entity
            )) {
                continue;
            }

            EntityResource::validateToOneRelation($entity, $relationship);
            $links[$relationship] = EntityResource::getStringId($entity);
        }

        foreach (
            $this->resource->getMetadata()->relationships->toMany
                as $relationship => $relation
        ) {
            $collection = $this->resource->callGetter($relationship);
            $collection = EntityResource::normalizeToManyRelation(
                $collection, $relationship
            );

            foreach ($collection as $entity) {
                if (!$this->securityContext->isGranted(
                    static::ACCESS_VIEW, $entity
                )) {
                    continue;
                }

                $links[$relationship][] = EntityResource::getStringId($entity);
            }
        }

        return $links;
    }
}
