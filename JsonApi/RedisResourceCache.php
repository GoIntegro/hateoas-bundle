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
// Redis.
use Predis\Client;

/**
 * @pattern multiton
 */
class RedisResourceCache implements ResourceCache
{
    const ERROR_CREATING_EXISTING_RESOURCE = "Creating a resource existing in the cache.";

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
     * @var Client
     */
    private $redis;

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
        $this->redis = new Client;
    }

    /**
     * @see ResourceCache::addResource
     */
    public function addResource(EntityResource $resource)
    {
        $key = $this->getKeyFromResource($resource);
        $this->redis->set($key, serialize($resource));

        return $this;
    }

    /**
     * @see ResourceCache::getResourceForEntity
     */
    public function getResourceForEntity(ResourceEntityInterface $entity)
    {
        $resource = NULL;

        if ($this->hasResourceForEntity($entity)) {
            $key = $this->getKeyFromEntity($entity);
            $resource = unserialize($this->redis->get($key));
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
            throw new Exception(self::ERROR_CREATING_EXISTING_RESOURCE);
        }

        return $resource;
    }

    /**
     * @param ResourceEntityInterface $entity
     * @return boolean
     */
    protected function hasResourceForEntity(ResourceEntityInterface $entity)
    {
        $key = $this->getKeyFromEntity($entity);

        return $this->redis->exists($key);
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

    /**
     * @param EntityResource $resource
     */
    private function getKeyFromResource(EntityResource $resource)
    {
        $class = $this->metadataCache->getReflection($resource->entity);

        return $class->getName() . '-' . $resource->id;
    }

    /**
     * @param ResourceEntityInterface $entity
     */
    private function getKeyFromEntity(ResourceEntityInterface $entity)
    {
        $class = $this->metadataCache->getReflection($entity);

        return $class->getName() . '-' . EntityResource::getStringId($entity);
    }
}
