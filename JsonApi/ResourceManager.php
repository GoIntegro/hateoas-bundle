<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\JsonApi;

// Metadata.
use GoIntegro\Bundle\HateoasBundle\Metadata\Resource\MetadataMinerInterface
    as MetadataMiner;
// DI.
use Symfony\Component\DependencyInjection\ContainerInterface;

class ResourceManager
{
    /**
     * @var MetadataMiner
     */
    private $metadataMiner;
    /**
     * @var ResourceCache
     */
    private $resourceCache;
    /**
     * @var ContainerInterface
     */
    private $serviceContainer;

    /**
     * @param MetadataMiner $metadataMiner
     * @param ResourceCache $resourceCache
     * @param ContainerInterface $serviceContainer
     */
    public function __construct(
        MetadataMiner $metadataMiner,
        ResourceCache $resourceCache,
        ContainerInterface $serviceContainer
    )
    {
        $this->metadataMiner = $metadataMiner;
        $this->resourceCache = $resourceCache;
        $this->serviceContainer = $serviceContainer;
    }

    /**
     * @return EntityResourceFactory
     */
    public function createResourceFactory()
    {
        return new EntityResourceFactory(
            $this->metadataMiner, $this->serviceContainer
        );
    }

    /**
     * @return ResourceCollectionFactory
     */
    public function createCollectionFactory()
    {
        return new ResourceCollectionFactory($this, $this->metadataMiner);
    }

    /**
     * @return DocumentFactory
     */
    public function createDocumentFactory()
    {
        return new DocumentFactory($this->resourceCache);
    }
}
