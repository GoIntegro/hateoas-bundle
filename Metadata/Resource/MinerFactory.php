<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\Metadata\Resource;

// Metadata.
use GoIntegro\Bundle\HateoasBundle\Metadata\Entity\MetadataCache;

/**
 * @pattern abstract factory
 */
class MinerFactory
{
    const ERROR_RESOURCE_ENTITY_EXPECTED = 'Se esperaba una entidad o fantasma de recurso.';

    /**
     * @var MetadataCache
     */
    private $metadataCache;
    /**
     * @var string
     */
    private $resourceClassPath;
    /**
     * @var \ReflectionClass
     */
    private $oreClass;

    /**
     * @param MetadataCache $metadataCache
     * @param string $resourceClassPath
     * @param \ReflectionClass $oreClass
     */
    public function __construct(
        MetadataCache $metadataCache,
        $resourceClassPath,
        \ReflectionClass $oreClass
    )
    {
        $this->metadataCache = $metadataCache;
        $this->resourceClassPath = $resourceClassPath;
        $this->oreClass = $oreClass;
    }

    /**
     * @return MetadataMinerInterface
     * @throws \InvalidArgumentException
     */
    public function create()
    {
        if ($this->oreClass->implementsInterface(
            MetadataMiner::GHOST_ENTITY_INTERFACE
        )) {
            $miner = new GhostMetadataMiner($this->metadataCache);
        } elseif ($this->oreClass->implementsInterface(
            MetadataMiner::RESOURCE_ENTITY_INTERFACE
        )) {
            $miner = new EntityMetadataMiner(
                $this->metadataCache, $this->resourceClassPath
            );
        } else {
            throw new \InvalidArgumentException(
                self::ERROR_RESOURCE_ENTITY_EXPECTED
            );
        }

        return $miner;
    }
}
