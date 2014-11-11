<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace JsonApi;

// Mocks.
use Codeception\Util\Stub;
// Serializers.
use GoIntegro\Bundle\HateoasBundle\JsonApi\DocumentSerializer;
// Tests.
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class DocumentSerializerTest extends TestCase
{
    const RESOURCE_TYPE = 'resources';

    public function testSerializingEmptyResourceDocument()
    {
        /* Given... (Fixture) */
        $resources = $this->createResourcesMock(0);
        $document = Stub::makeEmpty(
            'GoIntegro\Bundle\HateoasBundle\JsonApi\Document',
            [
                'wasCollection' => FALSE, // Key to this test.
                'resources' => $resources,
                'getResourceMeta' => function() { return []; }
            ]
        );
        $serializer = new DocumentSerializer($document);
        /* When... (Action) */
        $json = $serializer->serialize();
        /* Then... (Assertions) */
        $this->assertEquals(['resources' => NULL], $json);
    }

    public function testSerializingIndividualResourceDocument()
    {
        /* Given... (Fixture) */
        $resources = $this->createResourcesMock(1);
        $document = Stub::makeEmpty(
            'GoIntegro\Bundle\HateoasBundle\JsonApi\Document',
            [
                'wasCollection' => FALSE, // Key to this test.
                'resources' => $resources,
                'getResourceMeta' => function() { return []; }
            ]
        );
        $serializer = new DocumentSerializer($document);
        /* When... (Action) */
        $json = $serializer->serialize();
        /* Then... (Assertions) */
        $this->assertEquals(['resources' => [
            'id' => '0',
            'type' => self::RESOURCE_TYPE
        ]], $json);
    }

    public function testSerializingMultipleResourceDocument()
    {
        /* Given... (Fixture) */
        $resources = $this->createResourcesMock(3);
        $document = Stub::makeEmpty(
            'GoIntegro\Bundle\HateoasBundle\JsonApi\Document',
            [
                'wasCollection' => TRUE, // Key to this test.
                'resources' => $resources,
                'getResourceMeta' => function() { return []; }
            ]
        );
        $serializer = new DocumentSerializer($document);
        /* When... (Action) */
        $json = $serializer->serialize();
        /* Then... (Assertions) */
        $this->assertEquals(['resources' => [
            [
                'id' => '0',
                'type' => self::RESOURCE_TYPE
            ],
            [
                'id' => '1',
                'type' => self::RESOURCE_TYPE
            ],
            [
                'id' => '2',
                'type' => self::RESOURCE_TYPE
            ]
        ]], $json);
    }

    /**
     *
     */
    private function createResourcesMock($amount)
    {
        $metadata = Stub::makeEmpty(
            'GoIntegro\Bundle\HateoasBundle\Metadata\Resource\ResourceMetadata',
            [
                'type' => self::RESOURCE_TYPE,
                'subtype' => self::RESOURCE_TYPE,
                'fields' => []
            ]
        );

        $resources = [];
        for ($i = 0; $i < $amount; ++$i) {
            $resources[] = Stub::makeEmpty(
                'GoIntegro\Bundle\HateoasBundle\JsonApi\EntityResource',
                [
                    'id' => (string) $i,
                    'getMetadata' => function() use ($metadata) {
                        return $metadata;
                    }
                ]
            );
        }

        $collection = Stub::makeEmpty(
            'GoIntegro\Bundle\HateoasBundle\JsonApi\ResourceCollection',
            [
                'getMetadata' => function() use ($metadata) {
                    return $metadata;
                },
                'getIterator' => function() use ($resources) {
                    return new \ArrayIterator($resources);
                },
                'count' => function() use ($resources) {
                    return count($resources);
                }
            ]
        );

        return $collection;
    }
}
