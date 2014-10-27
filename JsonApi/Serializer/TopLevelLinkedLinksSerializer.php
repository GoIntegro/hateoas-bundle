<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\JsonApi\Serializer;

// Recursos REST.
use GoIntegro\Bundle\HateoasBundle\JsonApi\ResourceEntityInterface,
    GoIntegro\Bundle\HateoasBundle\JsonApi\EntityResource;
// Colecciones.
use Doctrine\Common\Collections\Collection as CollectionInterface;
// JSON-API.
use GoIntegro\Bundle\HateoasBundle\JsonApi\Document;
// Metadata.
use GoIntegro\Bundle\HateoasBundle\Metadata\Resource\ResourceRelationship;

/**
 * @see http://jsonapi.org/format/#document-structure-resource-relationships
 */
class TopLevelLinkedLinksSerializer implements SerializerInterface
{
    const ERROR_MISSING_MAPPING_FIELD = "Una de las relaciones declaradas o evaluadas como \"link-only\" posiblemente no tenga un campo de mapeo inverso en la entidad vinculada.",
        BY_PRIMARY_URL_PATTERN = '%s/%s?%s={%s.id}',
        TO_ONE_URL_PATTERN = '%s/%s/{%s.%s}',
        TO_MANY_URL_PATTERN = '%s/%s/{%s.id}/links/%s';

    public $document;
    /**
     * @var array
     */
    private $apiUrlPath;

    public function __construct(Document $document, $apiUrlPath)
    {
        $this->document = $document;
        $this->apiUrlPath = $apiUrlPath;
    }

    public function serialize()
    {
        $json = [];

        foreach ($this->document as $resource) {
            if (!$resource->getMetadata()->hasRelationships()) continue;

            $walk = function($relationship) use (&$json, $resource) {
                $this->addResourceLinks($resource, $relationship, $json);
            };

            $resource->getMetadata()->relationships->walk($walk);
        }

        return $json;
    }

    private function addResourceLinks(
        EntityResource $resource,
        ResourceRelationship $relationship,
        array &$json
    ) {
        $relationshipKey
            = self::buildRelationKey($resource, $relationship->name);

        $urlTemplate = NULL;

        if (
            $resource->getMetadata()
                ->isLinkOnlyRelationship($relationship->name)
        ) {
            $urlTemplate = $this->buildByPrimaryUrlTemplate(
                $resource, $relationship
            );
        } else {
            $urlTemplate = $this->buildByRelationshipUrlTemplate(
                $resource, $relationship
            );
        }

        $json[$relationshipKey] = [
            'href' => $urlTemplate,
            'type' => $relationship->type
        ];
    }

    /**
     * @param EntityResource $resource
     * @param string $relationshipName
     * @return string
     */
    public static function buildRelationKey(
        EntityResource $resource, $relationshipName
    )
    {
        return $resource->getMetadata()->type . '.' . $relationshipName;
    }

    /**
     * @param EntityResource $resource
     * @param ResourceRelationship $relationship
     * @return string|NULL
     */
    protected function buildByPrimaryUrlTemplate(
        EntityResource $resource, ResourceRelationship $relationship
    )
    {
        if (is_null($relationship->mappingField)) {
            throw new \Exception(self::ERROR_MISSING_MAPPING_FIELD);
        }

        return sprintf(
            self::BY_PRIMARY_URL_PATTERN,
            $this->apiUrlPath,
            $relationship->type,
            $relationship->mappingField,
            $resource->getMetadata()->type
        );
    }

    /**
     * @param EntityResource $resource
     * @param ResourceRelationship $relationship
     * @return string
     */
    protected function buildByRelationshipUrlTemplate(
        EntityResource $resource, ResourceRelationship $relationship
    )
    {
        return ResourceRelationship::TO_ONE == $relationship->kind
            ? $this->buildToOneUrlTemplate($resource, $relationship)
            : $this->buildToManyUrlTemplate($resource, $relationship);
    }

    /**
     * @param EntityResource $resource
     * @param ResourceRelationship $relationship
     * @return string
     */
    private function buildToOneUrlTemplate(
        EntityResource $resource, ResourceRelationship $relationship
    )
    {
        return sprintf(
            self::TO_ONE_URL_PATTERN,
            $this->apiUrlPath,
            $relationship->type,
            $resource->getMetadata()->type,
            $relationship->name
        );
    }

    /**
     * @param EntityResource $resource
     * @param ResourceRelationship $relationship
     * @return string
     */
    private function buildToManyUrlTemplate(
        EntityResource $resource, ResourceRelationship $relationship
    )
    {
        $metadata = $resource->getMetadata();

        return sprintf(
            self::TO_MANY_URL_PATTERN,
            $this->apiUrlPath,
            $metadata->type,
            $metadata->type,
            $relationship->name
        );
    }
}
