<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace JsonApi;

// Mocks.
use Codeception\Util\Stub;
// Recursos.
use GoIntegro\Bundle\HateoasBundle\JsonApi\ResourceManager;
// Tests.
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class ResourceManagerTest extends TestCase
{
    public function testCreatingResourceFactory()
    {
        /* Given... (Fixture) */
        $resourceManager = $this->createResourceManager();
        /* When... (Action) */
        $resourceFactory = $resourceManager->createResourceFactory();
        /* Then... (Assertions) */
        $this->assertInstanceOf(
            'GoIntegro\Bundle\HateoasBundle\JsonApi\EntityResourceFactory',
            $resourceFactory
        );
    }

    public function testCreatingCollectionFactory()
    {
        /* Given... (Fixture) */
        $resourceManager = $this->createResourceManager();
        /* When... (Action) */
        $collectionFactory = $resourceManager->createCollectionFactory();
        /* Then... (Assertions) */
        $this->assertInstanceOf(
            'GoIntegro\Bundle\HateoasBundle\JsonApi\ResourceCollectionFactory',
            $collectionFactory
        );
    }

    public function testCreatingSerializerFactory()
    {
        /* Given... (Fixture) */
        $resourceManager = $this->createResourceManager();
        /* When... (Action) */
        $serializerFactory = $resourceManager->createSerializerFactory();
        /* Then... (Assertions) */
        $this->assertInstanceOf(
            'GoIntegro\Bundle\HateoasBundle\JsonApi\SerializerFactory',
            $serializerFactory
        );
    }

    /**
     * @return ResourceManager
     */
    private function createResourceManager()
    {
        $resourceCache = Stub::makeEmpty(
            'GoIntegro\Bundle\HateoasBundle\JsonApi\ResourceCache'
        );
        $metadataMiner = Stub::makeEmpty(
            'GoIntegro\Bundle\HateoasBundle\Metadata\Resource\MetadataMinerInterface'
        );
        $requestParser = Stub::makeEmpty(
            'GoIntegro\Bundle\HateoasBundle\JsonApi\Request\Parser'
        );
        $serviceContainer = Stub::makeEmpty(
            'Symfony\Component\DependencyInjection\ContainerInterface'
        );
        $resourceManager = new ResourceManager(
            $resourceCache,
            $metadataMiner,
            $requestParser,
            $serviceContainer
        );

        return $resourceManager;
    }
}
