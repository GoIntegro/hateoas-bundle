<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\JsonApi;

// Iteración.
use IteratorAggregate, ArrayIterator, Countable;

/**
 * @todo ¿Implementar ResourceCollectionInterface?
 */
class TypedResourceCollection implements IteratorAggregate, Countable
{
    /**
     * @var ResourceCache
     */
    private $resourceCache;
    /**
     * @var array
     */
    private $resourcesByType = [];

    /**
     * @param ResourceCache $resourceCache
     */
    public function __construct(ResourceCache $resourceCache)
    {
        $this->resourceCache = $resourceCache;
    }

    /**
     * @param EntityResource $resource
     * @return self
     */
    public function addResource(EntityResource $resource)
    {
        $this->resourceCache->addResource($resource);
        $this->resourcesByType[$resource->getMetadata()->type][$resource->id] = $resource;

        return $this;
    }

    /**
     * @param ResourceEntityInterface $entity
     * @return self
     */
    public function addResourceForEntity(ResourceEntityInterface $entity)
    {
        $resource = $this->resourceCache->getResourceForEntity($entity);
        $this->resourcesByType[$resource->getMetadata()->type][$resource->id] = $resource;

        return $resource;
    }

    /**
     * @param string $type
     * @param string $id
     * @return boolean
     */
    public function hasResource($type, $id)
    {
        return isset($this->resourcesByType[$type][$id]);
    }

    /**
     * @param string $type
     * @param string $id
     * @return EntityResource
     */
    public function getResource($type, $id)
    {
        return $this->hasResource($type, $id)
            ? $this->resourcesByType[$type][$id]
            : NULL;
    }

    /**
     * @param ResourceEntityInterface $entity
     * @return EntityResource
     * @todo ¿Mantener _otro_ índice por clase de la entidad?
     */
    public function getResourceForEntity(ResourceEntityInterface $entity)
    {
        $resource = $this->resourceCache->getResourceForEntity($entity);

        return $this->hasResource($resource->getMetadata()->type, $resource->id)
            ? $resource
            : NULL;
    }

    /**
     * @see IteratorAggregate::getIterator
     */
    public function getIterator()
    {
        $flatList = [];
        $callback = function(array $itemList) use (&$flatList) {
            $flatList = array_merge($flatList, $itemList);
        };
        array_walk($this->resourcesByType, $callback);

        return new ArrayIterator($flatList);
    }

    /**
     * @return integer
     */
    public function count()
    {
        $amount = 0;

        foreach ($this->resourcesByType as $resources) {
            $amount += count($resources);
        }

        return $amount;
    }
}
