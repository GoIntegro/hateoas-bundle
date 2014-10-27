<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */

namespace GoIntegro\Bundle\HateoasBundle\Metadata\Resource;

// Interfaces.
use GoIntegro\Bundle\HateoasBundle\JsonApi\ResourceEntityInterface;
// Metadata.
use GoIntegro\Bundle\HateoasBundle\Metadata\Entity\MetadataCache;
// Datos.
use GoIntegro\Bundle\HateoasBundle\Util\Inflector;
// ORM.
use Doctrine\ORM\Mapping\ClassMetadata;
// Reflexión.
use GoIntegro\Bundle\HateoasBundle\Util\Reflection,
    ReflectionClass,
    ReflectionMethod;
// Recursos.
use GoIntegro\Bundle\HateoasBundle\JsonApi\EntityResource;
// Excepciones.
use Exception;

trait MiningTools
{
    /**
     * @param string|ResourceEntityInterface $entityClassName
     * @return ReflectionClass
     */
    public function getResourceClass($entityClassName)
    {
        $className = MetadataMiner::DEFAULT_RESOURCE_CLASS;
        $class = $this->metadataCache->getReflection($entityClassName);
        $parentClass = $class->getParentClass();
        $resourceClassName = $this->entityClassToResourceClass($class);

        if (class_exists($resourceClassName)) {
            $className = $resourceClassName;
        } elseif (
            !empty($parentClass)
            && $parentClass->implementsInterface(
                MetadataMiner::RESOURCE_ENTITY_INTERFACE
            )
        ) {
            $resourceClassName
                = $this->entityClassToResourceClass($parentClass);

            if (class_exists($resourceClassName)) {
                $className = $resourceClassName;
            }
        }

        return $this->metadataCache->getReflection($className);
    }

    /**
     * @param string|ResourceEntityInterface $entityClass
     * @return string
     */
    protected function parseSubtype($entityClassName)
    {
        $class = $this->metadataCache->getReflection($entityClassName);

        return Inflector::typify($class->getShortName());
    }
}
