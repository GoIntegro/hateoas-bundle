<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\JsonApi;

// Metadata.
use GoIntegro\Bundle\HateoasBundle\Metadata\Resource\MetadataMinerInterface as MetadataMiner;
// Request.
use GoIntegro\Bundle\HateoasBundle\JsonApi\Request\Parser as RequestParser;
// Servicios.
use Symfony\Component\DependencyInjection\ContainerInterface;

class ResourceManager
{
    /**
     * @var ResourceCache
     */
    public $resourceCache;
    /**
     * @var MetadataMiner
     */
    private $metadataMiner;
    /**
     * @var RequestParser
     */
    private $requestParser;
    /**
     * @var ContainerInterface
     */
    private $serviceContainer;
    /**
     * @var array
     */
    private $apiUrlPath;

    /**
     * @param ResourceCache $resourceCache
     * @param MetadataMiner $metadataMiner
     * @param RequestParser $requestParser
     * @param ContainerInterface $serviceContainer
     * @param string $apiUrlPath
     */
    public function __construct(
        ResourceCache $resourceCache,
        MetadataMiner $metadataMiner,
        RequestParser $requestParser,
        ContainerInterface $serviceContainer,
        $apiUrlPath = ''
    )
    {
        $this->resourceCache = $resourceCache;
        $this->metadataMiner = $metadataMiner;
        $this->requestParser = $requestParser;
        $this->serviceContainer = $serviceContainer;
        $this->apiUrlPath = $apiUrlPath;
    }

    /**
     * @return EntityResourceFactory
     */
    public function createResourceFactory()
    {
        return new EntityResourceFactory(
            $this->metadataMiner,
            $this->serviceContainer
        );
    }

    /**
     * @return ResourceCollectionFactory
     */
    public function createCollectionFactory()
    {
        return new ResourceCollectionFactory(
            $this,
            $this->metadataMiner,
            $this->requestParser
        );
    }

    /**
     * @return SerializerFactory
     */
    public function createSerializerFactory()
    {
        return new SerializerFactory(
            $this, $this->requestParser, $this->apiUrlPath
        );
    }
}
