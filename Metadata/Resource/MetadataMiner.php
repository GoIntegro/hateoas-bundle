<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\Metadata\Resource;

// Reflection.
use GoIntegro\Bundle\HateoasBundle\Util\Reflection;

/**
 * @pattern facade
 */
class MetadataMiner implements MetadataMinerInterface
{
    const RESOURCE_ENTITY_INTERFACE = 'GoIntegro\\Bundle\\HateoasBundle\\JsonApi\\ResourceEntityInterface',
        GHOST_ENTITY_INTERFACE = 'GoIntegro\\Bundle\\HateoasBundle\\JsonApi\\GhostResourceEntity',
        DEFAULT_RESOURCE_CLASS = 'GoIntegro\\Bundle\\HateoasBundle\\JsonApi\\EntityResource';

    /**
     * @var \Predis\Client
     */
    protected $redis;

    /**
     * @param MinerProvider
     */
    public function __construct(MinerProvider $minerProvider)
    {
        $this->minerProvider = $minerProvider;
        $this->redis = new \Predis\Client();
    }

    /**
     * @param \GoIntegro\Bundle\HateoasBundle\JsonApi\ResourceEntityInterface|string $ore
     * @param ResourceMetadata
     */
    public function mine($ore)
    {
        if (is_object($ore)) $ore = get_class($ore);

        if ($this->redis->exists($ore)) {
            $metadata = unserialize($this->redis->get($ore));
        } else {
            $metadata = $this->minerProvider->getMiner($ore)->mine($ore);
            $this->redis->del($ore);
            $this->redis->set($ore, serialize($metadata));
        }

        return $metadata;
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
