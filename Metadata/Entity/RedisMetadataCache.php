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
        return $this->arrayCache->getReflection($className);
    }

    /**
     * @see MetadataCache::getMapping
     */
    public function getMapping($className)
    {
        return $this->getFromRedis($className, 'mapping', __FUNCTION__);
    }

    /**
     * @see MetadataCache::getMapping
     */
    private function getFromRedis($className, $suffix, $methodName)
    {
        if (is_object($className)) $className = get_class($className);

        $mapping = NULL;
        $key = $className . '-' . $suffix;

        if (!$this->redis->exists($key)) {
            $mapping = $this->arrayCache->$method($className);
            $this->redis->set($key, serialize($mapping));
        } else {
            $mapping = unserialize($this->redis->get($key));
        }

        return $mapping;
    }
}
