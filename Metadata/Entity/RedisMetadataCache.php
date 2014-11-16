<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\Metadata\Entity;

// Reflexión.
use ReflectionClass;
// ORM.
use Doctrine\ORM\EntityManagerInterface;
// Excepciones.
use Exception;

/**
 * @pattern multiton
 */
class RedisMetadataCache implements MetadataCache
{
    /**
     * @var ArrayMetadataCache
     */
    protected $arrayCache;
    /**
     * @var \Predis\Client
     */
    protected $redis;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager) {
        $this->arrayCache = new ArrayMetadataCache($entityManager);
        $this->redis = new \Predis\Client();
    }

    /**
     * @param string $className
     * @return EntityMetadata
     */
    protected function getMetadata($className)
    {
        return $this->arrayCache->getMetadata($className);
    }

    /**
     * @see MetadataCache::getReflection
     */
    public function getReflection($className)
    {
        $reflection = NULL;
        $key = $className . '-reflection';

        if (!$this->redis->has($key)) {
            $reflection = $this->arrayCache->getMapping($className);
            $this->redis->set($className, serialize($metadata));
        } else {
            $reflection = unserialize($this->redis->get($key));
        }

        return $reflection;
    }

    /**
     * @see MetadataCache::getMapping
     */
    public function getMapping($className)
    {
        $mapping = NULL;
        $key = $className . '-mapping';

        if (!$this->redis->has($key)) {
            $mapping = $this->arrayCache->getMapping($className);
            $this->redis->set($className, serialize($metadata));
        } else {
            $mapping = unserialize($this->redis->get($key));
        }

        return $mapping;
    }
}
