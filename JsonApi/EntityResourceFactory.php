<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\JsonApi;

// Interfaces.
use GoIntegro\Interfaces\Factory,
    GoIntegro\Bundle\HateoasBundle\JsonApi\ResourceEntityInterface;
// Metadata.
use GoIntegro\Bundle\HateoasBundle\Metadata\Resource\MetadataMinerInterface;
// Reflexión.
use ReflectionClass;
// Servicios.
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @pattern AbstractFactory
 */
class EntityResourceFactory implements Factory
{
    const CONTAINER_AWARE_INTERFACE = 'Symfony\Component\DependencyInjection\ContainerAwareInterface';

    /**
     * @var MetadataMinerInterface
     */
    private $metadataMiner;
    /**
     * @var ContainerInterface
     */
    private $serviceContainer;
    /**
     * @var EntityResource
     */
    private $entity;

    /**
     * @param EntityManagerInterface $metadataMiner
     * @param ContainerInterface $serviceContainer
     */
    public function __construct(
        MetadataMinerInterface $metadataMiner,
        ContainerInterface $serviceContainer
    )
    {
        $this->metadataMiner = $metadataMiner;
        $this->serviceContainer = $serviceContainer;
    }

    /**
     * @param ResourceEntityInterface
     * @return self
     */
    public function setEntity(ResourceEntityInterface $entity)
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * @return EntityResource
     */
    public function create()
    {
        $metadata = $this->metadataMiner->mine($this->entity);
        $resource = $metadata
            ->resourceClass
            ->newInstance($this->entity, $metadata);

        if ($metadata->resourceClass->implementsInterface(
            self::CONTAINER_AWARE_INTERFACE
        )) {
            $resource->setContainer($this->serviceContainer);
        }

        return $resource;
    }
}
