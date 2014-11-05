<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\Metadata\Resource;

// Metadata.
use GoIntegro\Bundle\HateoasBundle\Metadata\Entity\MetadataCache,
    GoIntegro\Bundle\HateoasBundle\Metadata\Resource\ResourceRelationships;

class GhostMetadataMiner implements MetadataMinerInterface
{
    use MiningTools;

    private $metadataCache;

    /**
     * @param MetadataCache $metadataCache
     */
    public function __construct(MetadataCache $metadataCache)
    {
        $this->metadataCache = $metadataCache;
    }

    /**
     * @param \GoIntegro\Bundle\HateoasBundle\JsonApi\ResourceEntityInterface|string $entityClassName
     * @param ResourceMetadata
     */
    public function mine($entityClassName)
    {
        $type = $this->parseType($entityClassName);
        $subtype = $this->parseSubtype($entityClassName);
        $entityClass = $this->metadataCache->getReflection($entityClassName);
        $resourceClass = $this->getResourceClass($entityClass);
        $relationships
            = $entityClass->getMethod('getRelationships')->invoke(NULL);
        $fields = $this->getFields($entityClassName, $relationships);
        $pageSize = $resourceClass->getProperty('pageSize')->getValue();

        return new ResourceMetadata(
            $type, $subtype, $resourceClass, $fields, $relationships, $pageSize
        );
    }

    /**
     * @param \GoIntegro\Bundle\HateoasBundle\JsonApi\ResourceEntityInterface|string $entityClass
     * @return string
     * @todo ¿Subtipo para ghosts?
     */
    protected function parseType($entityClassName)
    {
        return $this->parseSubtype($entityClassName);
    }

    /**
     * @param \ReflectionClass $class
     * @return string
     */
    protected function entityClassToResourceClass(\ReflectionClass $class)
    {
        // @todo Parametrizar; "Resource" no estaba hardcodeado antes.
        return str_replace('Ghost', 'Resource', $class->getName())
            . 'Resource';
    }
}
