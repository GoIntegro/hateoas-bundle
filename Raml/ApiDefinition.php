<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle;

// ORM.
use Doctrine\ORM\EntityManagerInterface;
// Metadata.
use GoIntegro\Hateoas\Metadata\Entity\MetadataCache;

class ApiDefinition
{
    const RESOURCE_ENTITY_INTERFACE = 'GoIntegro\\Hateoas\\JsonApi\\ResourceEntityInterface';

    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var MetadataCache
     */
    private $metadataCache;

    /**
     * @param EntityManagerInterface $em
     * @param MetadataCache $metadataCache
     */
    public function __construct(
        EntityManagerInterface $em,
        MetadataCache $metadataCache
    )
    {
        $this->em = $em;
        $this->metadataCache = $metadataCache;
    }

    /**
     *
     */
    public function validate()
    {
        $entityClassNames = $this->em->getConfiguration()
            ->getMetadataDriverImpl()
            ->getAllClassNames();

        foreach ($entityClassNames as $entityClassName) {
            $class = $this->metadataCache->getReflection($entityClassName);

            if ($class->implementsInterface(self::RESOURCE_ENTITY_INTERFACE)) {
                // @todo
            }
        }
    }
}
