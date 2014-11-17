<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace JsonApi\Request;

// Mocks.
use Codeception\Util\Stub;
// Tests.
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
// Request.
use GoIntegro\Bundle\HateoasBundle\JsonApi\Request\ResourceLinksHydrant;

class ResourceLinksHydrantTest extends TestCase
{
    const AUTHOR_ENTITY = 'author-entity',
        COMMENT_ENTITIES = 'comment-entities';

    public function testParsingASimpleRequest()
    {
        // Given...
        $repository = Stub::makeEmpty(
            'Doctrine\\ORM\\EntityRepository',
            [
                '__call' => function($name) {
                    switch ($name) {
                        case 'findOneById':
                            return self::AUTHOR_ENTITY;
                            break;

                        case 'findById':
                            return self::COMMENT_ENTITIES;
                            break;
                    }
                }
            ]
        );
        $authorRelationship = Stub::makeEmpty('GoIntegro\\Bundle\\HateoasBundle\\Metadata\\Resource\\ResourceRelationship');
        $commentsRelationship = Stub::makeEmpty('GoIntegro\\Bundle\\HateoasBundle\\Metadata\\Resource\\ResourceRelationship');
        $relationships = Stub::makeEmpty(
            'GoIntegro\\Bundle\\HateoasBundle\\Metadata\\Resource\\ResourceRelationships',
            [
                'toOne' => ['author' => $authorRelationship],
                'toMany' => ['comments' => $commentsRelationship]
            ]
        );
        $metadata = Stub::makeEmpty(
            'GoIntegro\\Bundle\\HateoasBundle\\Metadata\\Resource\\ResourceMetadata',
            [
                'relationships' => $relationships,
                'isToOneRelationship' => TRUE,
                'isToManyRelationship' => TRUE
            ]
        );
        $em = Stub::makeEmpty(
            'Doctrine\\ORM\\EntityManagerInterface',
            ['getRepository' => $repository]
        );
        $mm = Stub::makeEmpty(
            'GoIntegro\\Bundle\\HateoasBundle\\Metadata\\Resource\\MetadataMinerInterface',
            ['mine' => $metadata]
        );
        $hydrant = new ResourceLinksHydrant($em, $mm);
        $params = Stub::makeEmpty(
            'GoIntegro\\Bundle\\HateoasBundle\\JsonApi\\Request\\Params',
            [
                'primaryClass'
                    => "HateoasInc\\Bundle\\ExampleBundle\\Entity\\User"
            ]
        );
        $resourceObject = [
            'content' => "Meh.",
            'links' => [
                'author' => '5',
                'comments' => ['45', '54', '67']
            ]
        ];
        // When...
        $hydrant->hydrate($params, $resourceObject);
        $expected = [
            'content' => "Meh.",
            'links' => [
                'author' => self::AUTHOR_ENTITY,
                'comments' => self::COMMENT_ENTITIES
            ]
        ];
        // Then...
        $this->assertEquals($expected, $resourceObject);
    }
}
