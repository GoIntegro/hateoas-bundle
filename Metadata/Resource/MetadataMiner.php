<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\Metadata\Resource;

/**
 * @pattern facade
 */
class MetadataMiner implements MetadataMinerInterface
{
    const RESOURCE_ENTITY_INTERFACE = 'GoIntegro\\Bundle\\HateoasBundle\\JsonApi\\ResourceEntityInterface',
        GHOST_ENTITY_INTERFACE = 'GoIntegro\\Bundle\\HateoasBundle\\JsonApi\\GhostResourceEntity',
        DEFAULT_RESOURCE_CLASS = 'GoIntegro\\Bundle\\HateoasBundle\\JsonApi\\EntityResource';

    /**
     * @param MinerProvider
     */
    public function __construct(MinerProvider $minerProvider)
    {
        $this->minerProvider = $minerProvider;
    }

    /**
     * @param \GoIntegro\Bundle\HateoasBundle\JsonApi\ResourceEntityInterface|string $ore
     * @param ResourceMetadata
     */
    public function mine($ore)
    {
        return $this->minerProvider->getMiner($ore)->mine($ore);
    }

    /**
     * @param string $type
     * @param string $subtype
     * @param ResourceMetadata
     */
    public function stub($type, $subtype = NULL)
    {
        if (empty($type)) $subtype = $type;

        $resourceClass = new \ReflectionClass(self::DEFAULT_RESOURCE_CLASS);
        $fields = new ResourceFields([]);
        $relationships = new ResourceRelationships;
        $pageSize = $resourceClass->getProperty('pageSize')->getValue();

        return new ResourceMetadata(
            $type, $subtype, $resourceClass, $fields, $relationships, $pageSize
        );
    }

    /**
     * @param \GoIntegro\Bundle\HateoasBundle\JsonApi\ResourceEntityInterface|string $ore
     * @return \ReflectionClass
     */
    public function getResourceClass($ore)
    {
        return $this->minerProvider->getMiner($ore)->getResourceClass($ore);
    }
}
