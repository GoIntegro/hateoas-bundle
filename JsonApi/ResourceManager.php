<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\JsonApi;

// Metadata.
use GoIntegro\Bundle\HateoasBundle\Metadata\Resource\MetadataMinerInterface as MetadataMiner;
// Servicios.
use Symfony\Component\DependencyInjection\ContainerInterface;

class ResourceManager
{
    /**
     * @var MetadataMiner
     */
    private $metadataMiner;
    /**
     * @var ContainerInterface
     */
    private $serviceContainer;
    /**
     * @var array
     */
    private $apiUrlPath;

    /**
     * @param MetadataMiner $metadataMiner
     * @param ContainerInterface $serviceContainer
     * @param string $apiUrlPath
     */
    public function __construct(
        MetadataMiner $metadataMiner,
        ContainerInterface $serviceContainer,
        $apiUrlPath = ''
    )
    {
        $this->metadataMiner = $metadataMiner;
        $this->serviceContainer = $serviceContainer;
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
        return new SerializerFactory($this, $this->apiUrlPath);
    }
}
