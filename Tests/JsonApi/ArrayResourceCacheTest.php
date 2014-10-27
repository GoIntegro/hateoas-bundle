<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */

namespace JsonApi;

// Mocks.
use Codeception\Util\Stub;
// Recursos.
use GoIntegro\Bundle\HateoasBundle\JsonApi\ArrayResourceCache;
// Tests.
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class ArrayResourceCacheTest extends TestCase
{
    public function testAddingAResource()
    {
        /* Given... (Fixture) */
        $entity = Stub::makeEmpty('GoIntegro\Entity\User');
        $entityResource = Stub::makeEmpty(
            'GoIntegro\Bundle\HateoasBundle\JsonApi\EntityResource',
            ['entity' => $entity]
        );
        $classReflection = Stub::makeEmpty(
            'ReflectionClass',
            ['getName' => function() { return 'GoIntegro\Entity\User'; }]
        );
        $metadataCache = Stub::makeEmpty(
            'GoIntegro\Bundle\HateoasBundle\Metadata\Entity\MetadataCache',
            [
                'getReflection'
                    => function($object) use ($entity, $classReflection) {
                        // @todo Ambos objetos son la misma referencia, sin embargo, por algún motivo, la comparación de identidad retorna FALSE (?)
                        if (serialize($object) === serialize($entity)) {
                            return $classReflection;
                        }
                    }
            ]
        );
        $metadataMiner = Stub::makeEmpty(
            'GoIntegro\Bundle\HateoasBundle\Metadata\Resource\MetadataMinerInterface'
        );
        $serviceContainer = Stub::makeEmpty(
            'Symfony\Component\DependencyInjection\ContainerInterface'
        );
        $resourceCache = new ArrayResourceCache(
            $metadataCache, $metadataMiner, $serviceContainer
        );
        /* When... (Action) */
        $sameCache = $resourceCache->addResource($entityResource);
        $sameResource = $resourceCache->getResourceForEntity($entity);
        /* Then... (Assertions) */
        $this->assertSame($resourceCache, $sameCache);
        $this->assertSame($entityResource, $sameResource);
    }
}
