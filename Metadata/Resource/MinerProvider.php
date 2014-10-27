<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */

namespace GoIntegro\Bundle\HateoasBundle\Metadata\Resource;

// Metadata.
use GoIntegro\Bundle\HateoasBundle\Metadata\Entity\MetadataCache;

/**
 * @pattern multiton
 */
class MinerProvider
{
    /**
     * @var array
     */
    private static $miners = [];
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
     * @param \GoIntegro\Bundle\HateoasBundle\JsonApi\ResourceEntityInterface|string
     * @return MetadataMinerInterface
     */
    public function getMiner($ore)
    {
        $oreClass = $this->metadataCache->getReflection($ore);
        $oreClassName = $oreClass->getName();

        if (empty(self::$miners[$oreClassName])) {
            self::$miners[$oreClassName] = $this->createMiner($oreClass);
        }

        return self::$miners[$oreClassName];
    }

    /**
     * @param \ReflectionClass $oreClass
     * @return MetadataMinerInterface
     * @throws \InvalidArgumentException
     */
    private function createMiner(\ReflectionClass $oreClass)
    {
        $factory = new MinerFactory(
            $this->metadataCache, $this->resourceClassPath, $oreClass
        );

        return $factory->create();
    }
}
