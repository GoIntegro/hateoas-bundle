<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
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
    const RESOURCE_ID = 'someId', RESOURCE_TYPE = 'resources';

    public function testSerializingEmptyResourceDocument()
    {
        /* Given... (Fixture) */
        $metadata = Stub::makeEmpty(
            'GoIntegro\Bundle\HateoasBundle\Metadata\Resource\ResourceMetadata',
            ['type' => self::RESOURCE_TYPE, 'subtype' => self::RESOURCE_TYPE]
        );
        $resources = Stub::makeEmpty(
            'GoIntegro\Bundle\HateoasBundle\JsonApi\ResourceCollection',
            [
                'getMetadata' => function() use ($metadata) {
                    return $metadata;
                },
                'getIterator' => function() {
                    return new \ArrayIterator;
                },
                'count' => function() { return 0; }
            ]
        );
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
        $metadata = Stub::makeEmpty(
            'GoIntegro\Bundle\HateoasBundle\Metadata\Resource\ResourceMetadata',
            [
                'type' => self::RESOURCE_TYPE,
                'subtype' => self::RESOURCE_TYPE,
                'fields' => []
            ]
        );
        $resource = Stub::makeEmpty(
            'GoIntegro\Bundle\HateoasBundle\JsonApi\EntityResource',
            [
                'id' => self::RESOURCE_ID,
                'getMetadata' => function() use ($metadata) {
                    return $metadata;
                }
            ]
        );
        $resources = Stub::makeEmpty(
            'GoIntegro\Bundle\HateoasBundle\JsonApi\ResourceCollection',
            [
                'getMetadata' => function() use ($metadata) {
                    return $metadata;
                },
                'getIterator' => function() use ($resource) {
                    return new \ArrayIterator([$resource]);
                },
                'count' => function() { return 1; }
            ]
        );
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
            'id' => self::RESOURCE_ID,
            'type' => self::RESOURCE_TYPE
        ]], $json);
    }
}
