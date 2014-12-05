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

    public function testCreatingDocumentFactory()
    {
        /* Given... (Fixture) */
        $resourceManager = $this->createResourceManager();
        /* When... (Action) */
        $serializerFactory = $resourceManager->createDocumentFactory();
        /* Then... (Assertions) */
        $this->assertInstanceOf(
            'GoIntegro\Bundle\HateoasBundle\JsonApi\DocumentFactory',
            $serializerFactory
        );
    }

    /**
     * @return ResourceManager
     */
    private function createResourceManager()
    {
        $metadataMiner = Stub::makeEmpty(
            'GoIntegro\\Bundle\\HateoasBundle\\Metadata\\Resource\\MetadataMinerInterface'
        );
        $resourceCache = Stub::makeEmpty(
            'GoIntegro\\Bundle\\HateoasBundle\\JsonApi\\ResourceCache'
        );
        $serviceContainer = Stub::makeEmpty(
            'Symfony\\Component\\DependencyInjection\\ContainerInterface'
        );
        $resourceManager = new ResourceManager(
            $metadataMiner,
            $resourceCache,
            $serviceContainer,
            self::buildSecurityContext()
        );

        return $resourceManager;
    }

    /**
     * @return \Symfony\Component\Security\Core\SecurityContextInterface
     */
    public static function buildSecurityContext()
    {
        return Stub::makeEmpty(
            'Symfony\\Component\\Security\\Core\\SecurityContextInterface',
            ['isGranted' => TRUE]
        );
    }
}
