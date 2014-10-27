<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */

namespace GoIntegro\Bundle\HateoasBundle\JsonApi\Serializer;

// Interfaces.
use GoIntegro\Bundle\HateoasBundle\JsonApi\ResourceEntityInterface,
    Doctrine\Common\Collections\Collection as CollectionInterface;
// Datos.
use GoIntegro\Bundle\HateoasBundle\Util\Inflector;
// Recursos.
use GoIntegro\Bundle\HateoasBundle\JsonApi\ResourceDocument,
    GoIntegro\Bundle\HateoasBundle\JsonApi\EntityResource;
// Excepciones.
use Exception;

class ResourceObjectSerializer implements SerializerInterface
{
    const ERROR_FIELD_IS_RELATIONSHIP = "El campo \"%s\" es en sí un recurso vinculado.",
        ERROR_UNKOWN_FIELD = "El campo \"%s\" no existe.";

    public $resource;
    public $fields = [];

    public function __construct(
        ResourceDocument $resource,
        array $sparseFields = []
    )
    {
        $this->resource = $resource;
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
                throw new Exception($message);
            }

            if ($this->resource->isFieldBlacklisted($field)) {
                $message = sprintf(self::ERROR_UNKOWN_FIELD, $field);
                throw new Exception($message);
            }

            try {
                $value = $this->resource->callGetter($field);
            } catch (Exception $e) {
                $message = sprintf(self::ERROR_UNKOWN_FIELD, $field);
                throw new Exception($message);
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

    private function getResourceLinks(ResourceDocument $resource)
    {
        $links = [];

        foreach (
            $this->resource->getMetadata()->relationships->toOne as $relationship => $relation
        ) {
            $entity = $this->resource->callGetter($relationship);
            EntityResource::validateToOneRelation($entity, $relationship);
            $links[$relationship] = EntityResource::getStringId($entity);
        }

        foreach (
            $this->resource->getMetadata()->relationships->toMany as $relationship => $relation
        ) {
            $collection = $this->resource->callGetter($relationship);
            $collection = EntityResource::normalizeToManyRelation(
                $collection, $relationship
            );
            $callback = function(ResourceEntityInterface $entity) {
                return EntityResource::getStringId($entity);
            };
            $links[$relationship] = $collection->map($callback)->toArray();
        }

        return $links;
    }
}
