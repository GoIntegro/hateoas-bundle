<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace Metadata\Resource;

// Mocks.
use Codeception\Util\Stub;
// Metadata.
use GoIntegro\Bundle\HateoasBundle\Metadata\Resource\EntityMetadataMiner;
// Tests.
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class EntityMetadataMinerTest extends TestCase
{
    const API_BASE_URL = '/api/v2',
        RESOURCE_CLASS_PATH = 'Rest2/Resource';

    public function testMiningMetadataFromBasicEntity()
    {
        // Given...
        $cache = self::createMetadataCache();
        $miner = new EntityMetadataMiner(
            $cache, self::API_BASE_URL, self::RESOURCE_CLASS_PATH
        );
        $metadata = $miner->mine('GoIntegro\Entity\Workspace');
        $this->assertEquals('workspaces', $metadata->type);
        $this->assertEquals('workspaces', $metadata->subtype);
        $this->assertInstanceOf('ReflectionClass', $metadata->resourceClass);
        $this->assertInstanceOf(
            'GoIntegro\Bundle\HateoasBundle\Metadata\Resource\ResourceFields',
            $metadata->fields
        );
        $this->assertEquals(0, count($metadata->fields));
        $this->assertInstanceOf(
            'GoIntegro\Bundle\HateoasBundle\Metadata\Resource\ResourceRelationships',
            $metadata->relationships
        );
        $this->assertEquals(0, count($metadata->relationships));
    }

    public function testMiningMetadataWithFields()
    {
        // Given...
        $getter = Stub::makeEmpty(
            'ReflectionMethod',
            [
                'getShortName' => function() { return 'getSomeProperty'; },
                'isPublic' => function() { return TRUE; },
                'isStatic' => function() { return FALSE; },
                'getNumberOfRequiredParameters' => function() { return 0; }
            ]
        );
        $injector = Stub::makeEmpty(
            'ReflectionMethod',
            [
                'getShortName' => function() { return 'injectAProperty'; },
                'isPublic' => function() { return TRUE; },
                'isStatic' => function() { return FALSE; },
                'getNumberOfRequiredParameters' => function() { return 0; }
            ]
        );
        $cache = self::createMetadataCache([$getter], [$injector]);
        $miner = new EntityMetadataMiner(
            $cache, self::API_BASE_URL, self::RESOURCE_CLASS_PATH
        );
        $metadata = $miner->mine('GoIntegro\Entity\Workspace');
        $this->assertEquals('workspaces', $metadata->type);
        $this->assertEquals('workspaces', $metadata->subtype);
        $this->assertInstanceOf('ReflectionClass', $metadata->resourceClass);
        $this->assertInstanceOf(
            'GoIntegro\Bundle\HateoasBundle\Metadata\Resource\ResourceFields',
            $metadata->fields
        );
        $this->assertEquals(2, count($metadata->fields));
        $this->assertContains('some-property', $metadata->fields->original);
        $this->assertContains('a-property', $metadata->fields->injected);
        $this->assertInstanceOf(
            'GoIntegro\Bundle\HateoasBundle\Metadata\Resource\ResourceRelationships',
            $metadata->relationships
        );
        $this->assertEquals(0, count($metadata->relationships));
    }

    public function testMiningMetadataWithRelationships()
    {
        // Given...
        $toOne = [
            'fieldName' => 'platform',
            'inversedBy' => 'workspaces',
            'targetEntity' => 'GoIntegro\\Entity\\Platform',
            'fetch' => 2,
            'type' => 2,
            'mappedBy' => NULL,
            'isOwningSide' => TRUE,
            'sourceEntity' => 'GoIntegro\\Entity\\Workspace'
        ];
        $toOneClass = Stub::makeEmpty(
            'ReflectionClass',
            [
                'getName' => function() { return 'GoIntegro\\Entity\\Platform'; }
            ]
        );

        $toMany = [
            'fieldName' => 'users',
            'targetEntity' => 'GoIntegro\\Entity\\User',
            'mappedBy' => NULL,
            'inversedBy' => 'workspacesJoined',
            'fetch' => 2,
            'type' => 8,
            'isOwningSide' => TRUE,
            'sourceEntity' => 'GoIntegro\\Entity\\Workspace'
        ];
        $toManyClass = Stub::makeEmpty(
            'ReflectionClass',
            [
                'getName' => function() { return 'GoIntegro\\Entity\\User'; }
            ]
        );

        $cache = self::createMetadataCache(
            [],
            [],
            [$toOne, $toMany],
            [
                'GoIntegro\\Entity\\Platform' => $toOneClass,
                'GoIntegro\\Entity\\User' => $toManyClass
            ]
        );
        $miner = new EntityMetadataMiner(
            $cache, self::API_BASE_URL, self::RESOURCE_CLASS_PATH
        );
        $metadata = $miner->mine('GoIntegro\Entity\Workspace');
        $this->assertEquals('workspaces', $metadata->type);
        $this->assertEquals('workspaces', $metadata->subtype);
        $this->assertInstanceOf('ReflectionClass', $metadata->resourceClass);
        $this->assertInstanceOf(
            'GoIntegro\Bundle\HateoasBundle\Metadata\Resource\ResourceFields',
            $metadata->fields
        );
        $this->assertEquals(0, count($metadata->fields));
        $this->assertInstanceOf(
            'GoIntegro\Bundle\HateoasBundle\Metadata\Resource\ResourceRelationships',
            $metadata->relationships
        );
        $this->assertEquals(2, count($metadata->relationships));
        $this->assertEquals(2, count($metadata->relationships->linkOnly));
    }

    /**
     * @param array $entityMethods
     * @param array $resourceMethods
     * @param array $associationMappings
     * @return MetadataCache
     */
    private function createMetadataCache(
        array $entityMethods = [],
        array $resourceMethods = [],
        array $associationMappings = [],
        array $reflectionClasses = []
    )
    {
        $mapping = Stub::makeEmpty(
            'Doctrine\ORM\Mapping\ClassMetadataInfo',
            [
                'getAssociationMappings'
                    => function() use ($associationMappings) {
                        return $associationMappings;
                    },
                'getName' => 'GoIntegro\Entity\Workspace'
            ]
        );
        $property = Stub::makeEmpty(
            'ReflectionProperty',
            ['getValue' => function() { return []; }]
        );
        $entityClass = Stub::makeEmpty(
            'ReflectionClass',
            [
                'getName' => 'GoIntegro\Entity\Workspace',
                'getShortName' => 'Workspace',
                'getMethods' => function() use ($entityMethods) {
                    return $entityMethods;
                },
                'getProperty'
                    => function() use ($property) { return $property; }
            ]
        );
        $resourceClass = Stub::makeEmpty(
            'ReflectionClass',
            [
                'getMethods' => $resourceMethods,
                'getProperty' => $property
            ]
        );
        $cache = Stub::makeEmpty(
            'GoIntegro\Bundle\HateoasBundle\Metadata\Entity\MetadataCache',
            [
                'getReflection'
                    => function($name) use (
                        $entityClass, $resourceClass, $reflectionClasses
                    ) {
                        switch ($name) {
                            case 'GoIntegro\\Entity\\Workspace':
                                return $entityClass;

                            case 'GoIntegro\\Bundle\\HateoasBundle\\JsonApi\\EntityResource':
                            case 'GoIntegro\\Rest2\\Resource\\WorkspaceResource':
                                return $resourceClass;

                            default:
                                return $reflectionClasses[$name];
                        }
                    },
                'getMapping' => $mapping
            ]
        );

        return $cache;
    }
}
