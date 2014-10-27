<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
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
class ArrayMetadataCache implements MetadataCache
{
    const ERROR_REFLECTION_EXPECTED_VALUE = "Sólo puede obtenerse la reflexión de una clase por su nombre o una instancia.",
        ERROR_MAPPING_EXPECTED_VALUE = "Sólo puede obtenerse la metadata de mapeo de una entidad por su clase o una instancia.";

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;
    /**
     * @var array
     */
    protected static $metadata = [];

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager) {
        $this->entityManager = $entityManager;
    }

    /**
     * @param string $className
     * @return EntityMetadata
     */
    protected function getMetadata($className)
    {
        if (!isset(self::$metadata[$className])) {
            self::$metadata[$className] = new EntityMetadata($className);
        }

        return self::$metadata[$className];
    }

    /**
     * @see MetadataCache::getReflection
     */
    public function getReflection($className)
    {
        if (!is_string($className) && !is_object($className)) {
            throw new Exception(self::ERROR_REFLECTION_EXPECTED_VALUE);
        }

        $this->stringify($className);
        $metadata = $this->getMetadata($className);

        if (empty($metadata->reflection)) {
            $metadata->reflection = new ReflectionClass($className);
        }

        return $metadata->reflection;
    }

    /**
     * @see MetadataCache::getMapping
     */
    public function getMapping($className)
    {
        if (!is_string($className) && !is_object($className)) {
            throw new Exception(self::ERROR_MAPPING_EXPECTED_VALUE);
        }

        $this->stringify($className);
        $metadata = $this->getMetadata($className);

        if (empty($metadata->mapping)) {
            $metadata->mapping
                = $this->entityManager->getClassMetadata($className);
        }

        return $metadata->mapping;
    }

    /**
     * @param &$value mixed
     * @return $string
     * @throws Exception
     */
    protected function stringify(&$value)
    {
        if (
            !is_string($value)
            and !$value = get_class($value)
        ) {
            $message = "El parámetro no es una cadena de caracteres ni una instancia.";
            throw new Exception($message);
        }

        return $value;
    }
}
