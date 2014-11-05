<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
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
use GoIntegro\Bundle\HateoasBundle\Util\Reflection;
// Recursos.
use GoIntegro\Bundle\HateoasBundle\JsonApi\EntityResource;
// Excepciones.
use Exception;

trait MiningTools
{
    /**
     * @var array
     * @see http://jsonapi.org/format/#document-structure-resource-object-attributes
     */
    private static $reservedGetters = ["getId", "getType", "getHref", "getLinks"];

    /**
     * @param string|ResourceEntityInterface $entityClassName
     * @return \ReflectionClass
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

    /**
     * @param \GoIntegro\Bundle\HateoasBundle\JsonApi\ResourceEntityInterface|string $entityClassName
     * @param ResourceRelationships $relationships
     * @return array
     * @todo Publicar o eliminar el parámetro $entityClassName.
     */
    protected function getFields(
        $entityClassName,
        ResourceRelationships $relationships
    )
    {
        $fields = [];
        $class = $this->metadataCache->getReflection($entityClassName);

        foreach ($class->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            if (in_array($method->getShortName(), self::$reservedGetters)) {
                continue;
            }

            if (Reflection::isMethodGetter($method)) {
                $fields[] = Inflector::hyphenate(
                    substr($method->getShortName(), 3)
                );
            }
        }

        foreach (ResourceRelationships::$kinds as $kind) {
            $fields = array_diff($fields, array_keys($relationships->$kind));
        }

        $fields = array_diff($fields, $relationships->dbOnly);

        return new ResourceFields($fields);
    }
}
