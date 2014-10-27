<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\JsonApi;

// Metadata.
use GoIntegro\Bundle\HateoasBundle\Metadata\Entity\MetadataCache,
    GoIntegro\Bundle\HateoasBundle\Metadata\Resource\MetadataMinerInterface;
// Excepciones.
use Exception;
// Servicios.
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @pattern multiton
 */
class ArrayResourceCache implements ResourceCache
{
    /**
     * @var array
     */
    private static $resourcesByClass;
    /**
     * @var MetadataCache
     */
    private $metadataCache;
    /**
     * @var MetadataMinerInterface
     */
    private $metadataMiner;
    /**
     * @var ContainerInterface
     */
    private $serviceContainer;

    /**
     * @param MetadataCache $metadataCache
     * @param MetadataMinerInterface $metadataMiner
     * @param ContainerInterface $serviceContainer
     */
    public function __construct(
        MetadataCache $metadataCache,
        MetadataMinerInterface $metadataMiner,
        ContainerInterface $serviceContainer
    )
    {
        $this->metadataCache = $metadataCache;
        $this->metadataMiner = $metadataMiner;
        $this->serviceContainer = $serviceContainer;
    }

    /**
     * @see ResourceCache::addResource
     */
    public function addResource(EntityResource $resource)
    {
        $class = $this->metadataCache->getReflection($resource->entity);
        self::$resourcesByClass[$class->getName()][$resource->id] = $resource;

        return $this;
    }

    /**
     * @see ResourceCache::getResourceForEntity
     */
    public function getResourceForEntity(ResourceEntityInterface $entity)
    {
        $resource = NULL;

        if ($this->hasResourceForEntity($entity)) {
            $class = $this->metadataCache->getReflection($entity);
            $id = EntityResource::getStringId($entity);
            $resource = self::$resourcesByClass[$class->getName()][$id];
        } else {
            $resource = $this->createResource($entity);
        }

        return $resource;
    }

    /**
     * @param ResourceEntityInterface $entity
     * @return EntityResource
     */
    protected function createResource(ResourceEntityInterface $entity)
    {
        if (!$this->hasResourceForEntity($entity)) {
            $resource = $this->createResourceFactory()
                ->setEntity($entity)
                ->create();
            $this->addResource($resource);
        } else {
            throw new Exception("Creando un recurso existente en el cache.");
        }

        return $resource;
    }

    /**
     * @param ResourceEntityInterface $entity
     * @return boolean
     */
    protected function hasResourceForEntity(ResourceEntityInterface $entity)
    {
        $class = $this->metadataCache->getReflection($entity);
        $id = EntityResource::getStringId($entity);

        return isset(self::$resourcesByClass[$class->getName()][$id]);
    }

    /**
     * @return EntityResourceFactory
     */
    private function createResourceFactory()
    {
        return new EntityResourceFactory(
            $this->metadataMiner,
            $this->serviceContainer
        );
    }
}
