<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\Raml;

// ORM.
use Doctrine\ORM\EntityManagerInterface;
// Metadata.
use GoIntegro\Hateoas\Metadata\Entity\MetadataCache;
// RAML.
use GoIntegro\Raml\DocNavigator;
// Utils.
use GoIntegro\Hateoas\Util;

class ApiDefinition
{
    const RESOURCE_ENTITY_INTERFACE = 'GoIntegro\\Hateoas\\JsonApi\\ResourceEntityInterface';

    const ERROR_MISSING_ENTITY = "No entity matches the resource \"%s\".",
        ERROR_ENTITIES_PER_RESOURCE = "The resource \"%s\" listed in the RAML doc matches the following entity class names: \"%s\". If you want to keep the short-names of these resource entities you need to map all but one of them to other resource types in the bundle configuration.";

    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var MetadataCache
     */
    private $metadataCache;
    /**
     * @var DocNavigator
     */
    private $docNavigator;
    /**
     * @var array
     */
    private $indexedClassNames;

    /**
     * @param EntityManagerInterface $em
     * @param MetadataCache $metadataCache
     * @param DocNavigator $docNavigator
     */
    public function __construct(
        EntityManagerInterface $em,
        MetadataCache $metadataCache,
        DocNavigator $docNavigator
    )
    {
        $this->em = $em;
        $this->metadataCache = $metadataCache;
        $this->docNavigator = $docNavigator;
        $this->indexedClassNames = $this->indexEntityClassNames();
    }

    /**
     * @return array
     */
    private function indexEntityClassNames()
    {
        $indexedClassNames = [];
        $entityClassNames = $em->getConfiguration()
            ->getMetadataDriverImpl()
            ->getAllClassNames();

        foreach ($entityClassNames as $name) {
            $resourceType = Util\Inflector::typify($name);
            $indexedClassNames[$resourceType][] = $name;
        }

        return $indexedClassNames;
    }

    /**
     * @return array
     * @throws ApiDefinitionException
     * @todo The configuration doesn't actually allow overridding resource type to entity class mappings as the error message suggests. Oops.
     */
    public function map()
    {
        $map = [];

        foreach ($this->docNavigator->getDoc()->getResourceTypes() as $type) {
            $resourceClasses = $this->getResourceClasses($type);

            if (1 < count($resourceClasses)) {
                $message = sprintf(
                    self::ERROR_ENTITIES_PER_RESOURCE,
                    $type,
                    implode(', ', $resourceClasses)
                );
                throw new ApiDefinitionException($message);
            }

            $map[$type] = reset($resourceClasses);
        }

        return $map;
    }

    /**
     * @param string $type
     * @return array
     * @throws ApiDefinitionException
     */
    private function getResourceClasses($type)
    {
        if (empty($this->indexedClassNames[$type])) {
            $message = sprintf(self::ERROR_MISSING_ENTITY, $type);
            throw new ApiDefinitionException($message);
        }

        $resourceClasses = [];

        foreach ($this->indexEntityClassNames[$type] as $className) {
            $class = $this->metadataCache->getReflection($className);

            if ($class->implementsInterface(self::RESOURCE_ENTITY_INTERFACE)) {
                $resourceClasses[] = $className;
            }
        }

        return $resourceClasses;
    }
}
