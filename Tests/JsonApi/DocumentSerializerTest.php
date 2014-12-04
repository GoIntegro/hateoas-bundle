<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\JsonApi;

// Mocks.
use Codeception\Util\Stub;
// Tests.
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class DocumentSerializerTest extends TestCase
{
    const RESOURCE_TYPE = 'resources';

    public function testSerializingEmptyResourceDocument()
    {
        /* Given... (Fixture) */
        $resources = self::createResourcesMock(0);
        $document = Stub::makeEmpty(
            'GoIntegro\Bundle\HateoasBundle\JsonApi\Document',
            [
                'wasCollection' => FALSE, // Key to this test.
                'resources' => $resources,
                'getResourceMeta' => function() { return []; }
            ]
        );
        $serializer = new DocumentSerializer(
            self::buildTopLevelLinksSerializer(),
            self::buildLinkedResourcesSerializer(),
            self::buildMetadataSerializer(),
            self::buildSecurityContext()
        );
        /* When... (Action) */
        $json = $serializer->serialize($document);
        /* Then... (Assertions) */
        $this->assertEquals(['resources' => NULL], $json);
    }

    public function testSerializingIndividualResourceDocument()
    {
        /* Given... (Fixture) */
        $resources = self::createResourcesMock(1);
        $document = Stub::makeEmpty(
            'GoIntegro\Bundle\HateoasBundle\JsonApi\Document',
            [
                'wasCollection' => FALSE, // Key to this test.
                'resources' => $resources,
                'getResourceMeta' => function() { return []; }
            ]
        );
        $serializer = new DocumentSerializer(
            self::buildTopLevelLinksSerializer(),
            self::buildLinkedResourcesSerializer(),
            self::buildMetadataSerializer(),
            self::buildSecurityContext()
        );
        /* When... (Action) */
        $json = $serializer->serialize($document);
        /* Then... (Assertions) */
        $this->assertEquals(['resources' => [
            'id' => '0',
            'type' => self::RESOURCE_TYPE
        ]], $json);
    }

    public function testSerializingMultipleResourceDocument()
    {
        /* Given... (Fixture) */
        $resources = self::createResourcesMock(3);
        $document = Stub::makeEmpty(
            'GoIntegro\Bundle\HateoasBundle\JsonApi\Document',
            [
                'wasCollection' => TRUE, // Key to this test.
                'resources' => $resources,
                'getResourceMeta' => function() { return []; }
            ]
        );
        $serializer = new DocumentSerializer(
            self::buildTopLevelLinksSerializer(),
            self::buildLinkedResourcesSerializer(),
            self::buildMetadataSerializer(),
            self::buildSecurityContext()
        );
        /* When... (Action) */
        $json = $serializer->serialize($document);
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
     * @param integer $amount
     * @param integer $offset
     * @return \GoIntegro\Bundle\HateoasBundle\JsonApi\ResourceCollection
     */
    private static function createResourcesMock($amount, $offset = 0)
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
                    'id' => (string) $offset,
                    'getMetadata' => function() use ($metadata) {
                        return $metadata;
                    }
                ]
            );
            ++$offset;
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

    /**
     * @return Serializer\TopLevelLinksSerializer
     */
    public static function buildTopLevelLinksSerializer()
    {
        return Stub::makeEmpty(
            'GoIntegro\\Bundle\\HateoasBundle\\JsonApi\\Serializer\\TopLevelLinksSerializer'
        );
    }

    /**
     * @return Serializer\LinkedResourcesSerializer
     */
    public static function buildLinkedResourcesSerializer()
    {
        return Stub::makeEmpty(
            'GoIntegro\\Bundle\\HateoasBundle\\JsonApi\\Serializer\\LinkedResourcesSerializer'
        );
    }

    /**
     * @return Serializer\MetadataSerializer
     */
    public static function buildMetadataSerializer()
    {
        return Stub::makeEmpty(
            'GoIntegro\\Bundle\\HateoasBundle\\JsonApi\\Serializer\\MetadataSerializer'
        );
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
