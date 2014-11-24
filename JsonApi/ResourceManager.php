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
// Security.
use Symfony\Component\Security\Core\SecurityContextInterface;

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
     * @var SecurityContextInterface
     */
    private $securityContext;
    /**
     * @var array
     */
    private $apiUrlPath;

    /**
     * @param MetadataMiner $metadataMiner
     * @param ResourceCache $resourceCache
     * @param ContainerInterface $serviceContainer
     * @param SecurityContextInterface $securityContext
     * @param string $apiUrlPath
     */
    public function __construct(
        MetadataMiner $metadataMiner,
        ResourceCache $resourceCache,
        ContainerInterface $serviceContainer,
        SecurityContextInterface $securityContext,
        $apiUrlPath = ''
    )
    {
        $this->metadataMiner = $metadataMiner;
        $this->resourceCache = $resourceCache;
        $this->serviceContainer = $serviceContainer;
        $this->securityContext = $securityContext;
        $this->apiUrlPath = $apiUrlPath;
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
     * @return SerializerFactory
     */
    public function createSerializerFactory()
    {
        return new SerializerFactory(
            $this->resourceCache, $this->securityContext, $this->apiUrlPath
        );
    }
}
