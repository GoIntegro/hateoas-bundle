<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\Metadata\Resource;

// Metadata.
use GoIntegro\Bundle\HateoasBundle\Metadata\Entity\MetadataCache;
// Datos.
use GoIntegro\Bundle\HateoasBundle\Util\Inflector;
// ORM.
use Doctrine\ORM\Mapping\ClassMetadata;
// Reflection.
use GoIntegro\Bundle\HateoasBundle\Util\Reflection;

class EntityMetadataMiner implements MetadataMinerInterface
{
    use MiningTools;

    const ERROR_MAPPING_TYPE_UNKNOWN = "El tipo de mapeo es desconocido.";

    /**
     * @var MetadataCache
     */
    private $metadataCache;
    /**
     * @var string
     */
    private $resourceClassPath;

    /**
     * @param MetadataCache $metadataCache
     * @param string $resourceClassPath
     */
    public function __construct(
        MetadataCache $metadataCache, $resourceClassPath
    )
    {
        $this->metadataCache = $metadataCache;
        $this->resourceClassPath = $resourceClassPath;
    }

    /**
     * @param \GoIntegro\Bundle\HateoasBundle\JsonApi\ResourceEntityInterface|string $entityClassName
     * @param ResourceMetadata
     */
    public function mine($entityClassName)
    {
        $type = $this->parseType($entityClassName);
        $subtype = $this->parseSubtype($entityClassName);
        $resourceClass = $this->getResourceClass($entityClassName);
        $relationships = $this->getRelationships($entityClassName, $type);
        $fields = $this->getFields($entityClassName, $relationships);
        $pageSize = $resourceClass->getProperty('pageSize')->getValue();

        return new ResourceMetadata(
            $type, $subtype, $resourceClass, $fields, $relationships, $pageSize
        );
    }

    /**
     * @param \GoIntegro\Bundle\HateoasBundle\JsonApi\ResourceEntityInterface|string $entityClass
     * @return string
     */
    protected function parseType($entityClassName)
    {
        $entityClassName = $this->getEntityClass($entityClassName);

        return $this->parseSubtype($entityClassName);
    }

    /**
     * @param \GoIntegro\Bundle\HateoasBundle\JsonApi\ResourceEntityInterface|string $entityClass
     * @return string
     */
    protected function getEntityClass($entityClassName)
    {
        $mapping = $this->metadataCache->getMapping($entityClassName);

        if (!empty($mapping->parentClasses)) {
            $parentClassName = reset($mapping->parentClasses);
            $parentClass
                = $this->metadataCache->getReflection($parentClassName);

            if ($parentClass->implementsInterface(
                MetadataMiner::RESOURCE_ENTITY_INTERFACE
            )) {
                $entityClassName = $parentClassName;
            }
        }

        return $entityClassName;
    }

    /**
     * @param \GoIntegro\Bundle\HateoasBundle\JsonApi\ResourceEntityInterface|string $entityClassName
     * @param string $primaryType
     * @return array
     * @todo Publicar o eliminar el parámetro $entityClassName.
     */
    protected function getRelationships($entityClassName, $primaryType)
    {
        $relationships = new ResourceRelationships;
        $metadata = $this->metadataCache->getMapping($entityClassName);
        $class = $this->metadataCache->getReflection($entityClassName);

        foreach ($metadata->getAssociationMappings() as $name => $mapping) {
            $relationshipName = Inflector::hyphenate($name);
            $mappingClass = $this->metadataCache->getReflection(
                $mapping['targetEntity']
            );

            if (self::isDbOnlyRelation($class, $mappingClass, $name)) {
                $relationships->dbOnly[] = $relationshipName;
                continue;
            }

            $className = $mappingClass->getName();
            $mappingField = $mapping['mappedBy'] ?: $mapping['inversedBy'];
            $relationshipKind = $this->getRelationshipKind($mapping['type']);
            $relationship = new ResourceRelationship(
                $className,
                $this->parseType($className),
                $this->parseSubtype($className),
                $relationshipKind,
                $relationshipName,
                $mappingField
            );

            if (self::isLinkOnlyRelation($class, $name)) {
                $relationshipKind = 'linkOnly';
            }

            $relationshipsPerKind = &$relationships->$relationshipKind;
            $relationshipsPerKind[$relationshipName] = $relationship;
        }

        return $relationships;
    }

    /**
     * Para relaciones que no van en el campo "links" del resource object.
     * @param \ReflectionClass $class
     * @param string $name
     * @return boolean
     * @see http://jsonapi.org/format/#document-structure-url-templates
     */
    private static function isLinkOnlyRelation(
        \ReflectionClass $class,
        $name
    )
    {
        $getterName = 'get' . ucfirst($name);

        return !$class->hasMethod($getterName)
            || !Reflection::isMethodGetter($class->getMethod($getterName));
    }

    /**
     * @param \ReflectionClass $class
     * @param \ReflectionClass $mappingClass
     * @param string $name
     * @return boolean
     */
    private static function isDbOnlyRelation(
        \ReflectionClass $class,
        \ReflectionClass $mappingClass,
        $name
    )
    {
        return !self::isLinkOnlyRelation($class, $name)
            && !$mappingClass->implementsInterface(
                MetadataMiner::RESOURCE_ENTITY_INTERFACE
            );
    }

    /**
     * @param string $mappingType
     * @return string
     * @throws \Exception
     */
    private function getRelationshipKind($mappingType)
    {
        switch ($mappingType) {
            case ClassMetadata::ONE_TO_ONE:
            case ClassMetadata::MANY_TO_ONE:
                return 'toOne';

            case ClassMetadata::ONE_TO_MANY:
            case ClassMetadata::MANY_TO_MANY:
                return 'toMany';

            default:
                throw new \Exception(self::ERROR_MAPPING_TYPE_UNKNOWN);
        }
    }
}
