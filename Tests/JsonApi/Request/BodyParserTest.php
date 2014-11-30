<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\JsonApi\Request;

// Mocks.
use Codeception\Util\Stub;
// Tests.
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class BodyParserTest extends TestCase
{
    const API_BASE_URL = '/api/v1',
        RESOURCE_TYPE = 'users';

    const HTTP_POST_BODY = <<<'JSON'
{
    "users": {
        "name": "John",
        "surname": "Connor"
    }
}
JSON;

    const HTTP_PUT_BODY = <<<'JSON'
{
    "users": {
        "id": "7",
        "name": "John",
        "surname": "Connor"
    }
}
JSON;

    public function testParsingARequestWithACreateBody()
    {
        // Given...
        $queryOverrides = [
            'getContent' => function() { return self::HTTP_POST_BODY; }
        ];
        $request = self::createRequest(
            '/api/v1/users',
            $queryOverrides,
            Parser::HTTP_POST,
            self::HTTP_POST_BODY
        );
        $action = Stub::makeEmpty(
            'GoIntegro\\Bundle\\HateoasBundle\\JsonApi\\Request\\RequestAction',
            [
                'name' => RequestAction::ACTION_CREATE,
                'target' => RequestAction::TARGET_RESOURCE
            ]
        );
        $params = Stub::makeEmpty(
            'GoIntegro\\Bundle\\HateoasBundle\\JsonApi\\Request\\Params',
            [
                'primaryType' => self::RESOURCE_TYPE,
                'action' => $action
            ]
        );
        $hydrant = Stub::makeEmpty('GoIntegro\\Bundle\\HateoasBundle\\JsonApi\\Request\\ResourceLinksHydrant');
        $parser = new BodyParser(
            self::createJsonCoder(),
            self::createDocFinder(),
            $hydrant,
            self::createCreationBodyParser(),
            self::createMutationBodyParser(),
            self::createLinkingBodyParser(),
            self::createUnlinkingBodyParser(),
            self::createTranslationsParser()
        );
        // When...
        $resources = $parser->parse($request, $params);
        // Then...
        $this->assertSame([[
            'name' => 'John',
            'surname' => 'Connor'
        ]], $resources);
    }

    public function testParsingARequestWithAnUpdateBody()
    {
        // Given...
        $queryOverrides = [
            'getContent' => function() { return self::HTTP_PUT_BODY; }
        ];
        $request = self::createRequest(
            '/api/v1/users',
            $queryOverrides,
            Parser::HTTP_PUT,
            self::HTTP_PUT_BODY
        );
        $action = Stub::makeEmpty(
            'GoIntegro\\Bundle\\HateoasBundle\\JsonApi\\Request\\RequestAction',
            [
                'name' => RequestAction::ACTION_UPDATE,
                'target' => RequestAction::TARGET_RESOURCE
            ]
        );
        $params = Stub::makeEmpty(
            'GoIntegro\\Bundle\\HateoasBundle\\JsonApi\\Request\\Params',
            [
                'primaryType' => self::RESOURCE_TYPE,
                'action' => $action
            ]
        );
        $hydrant = Stub::makeEmpty('GoIntegro\\Bundle\\HateoasBundle\\JsonApi\\Request\\ResourceLinksHydrant');
        $parser = new BodyParser(
            self::createJsonCoder(),
            self::createDocFinder(),
            $hydrant,
            self::createCreationBodyParser(),
            self::createMutationBodyParser(),
            self::createLinkingBodyParser(),
            self::createUnlinkingBodyParser(),
            self::createTranslationsParser()
        );
        // When...
        $resources = $parser->parse($request, $params);
        // Then...
        $this->assertSame([
            '7' => [
                'id' => '7',
                'name' => 'John',
                'surname' => 'Connor'
            ]
        ], $resources);
    }

    public function testParsingARequestWithARelateBody()
    {
        // Given...
        $queryOverrides = [
            'getContent' => function() { return self::HTTP_PUT_BODY; }
        ];
        $request = self::createRequest(
            '/api/v1/users',
            $queryOverrides,
            Parser::HTTP_PUT,
            self::HTTP_PUT_BODY
        );
        $action = Stub::makeEmpty(
            'GoIntegro\\Bundle\\HateoasBundle\\JsonApi\\Request\\RequestAction',
            [
                'name' => RequestAction::ACTION_UPDATE,
                'target' => RequestAction::TARGET_RELATIONSHIP
            ]
        );
        $params = Stub::makeEmpty(
            'GoIntegro\\Bundle\\HateoasBundle\\JsonApi\\Request\\Params',
            [
                'primaryType' => self::RESOURCE_TYPE,
                'action' => $action
            ]
        );
        $hydrant = Stub::makeEmpty('GoIntegro\\Bundle\\HateoasBundle\\JsonApi\\Request\\ResourceLinksHydrant');
        $parser = new BodyParser(
            self::createJsonCoder(),
            self::createDocFinder(),
            $hydrant,
            self::createCreationBodyParser(),
            self::createMutationBodyParser(),
            self::createLinkingBodyParser(),
            self::createUnlinkingBodyParser(),
            self::createTranslationsParser()
        );
        // When...
        $resources = $parser->parse($request, $params);
        // Then...
        $this->assertSame([
            '7' => [
                'links' => [
                    'user-groups' => []
                ]
            ]
        ], $resources);
    }

    /**
     * @param string $pathInfo
     * @param array $queryOverrides
     * @param string $method
     * @param string $body
     * @return Request
     */
    private static function createRequest(
        $pathInfo,
        array $queryOverrides,
        $method = Parser::HTTP_GET,
        $body = NULL
    )
    {
        $defaultOverrides = [
            'getIterator' => function() { return new \ArrayIterator([]); }
        ];
        $queryOverrides = array_merge($defaultOverrides, $queryOverrides);
        $query = Stub::makeEmpty(
            'Symfony\Component\HttpFoundation\ParameterBag',
            $queryOverrides
        );
        $request = Stub::makeEmpty(
            'Symfony\Component\HttpFoundation\Request',
            [
                'query' => $query,
                'request' => new \stdClass,
                'attributes' => new \stdClass,
                'cookies' => new \stdClass,
                'files' => new \stdClass,
                'server' => new \stdClass,
                'headers' => new \stdClass,
                'getPathInfo' => $pathInfo,
                'getMethod' => $method,
                'getContent' => $body
            ]
        );

        return $request;
    }

    /**
     * @return \GoIntegro\Bundle\HateoasBundle\Util\JsonCoder
     */
    private static function createJsonCoder()
    {
        $jsonCoder = Stub::makeEmpty(
            'GoIntegro\Bundle\HateoasBundle\Util\JsonCoder',
            [
                'decode' => function($json) {
                    return json_decode($json, TRUE);
                },
                'matchSchema' => TRUE
            ]
        );

        return $jsonCoder;
    }

    /**
     * @return \GoIntegro\Bundle\HateoasBundle\Raml\DocFinder
     */
    private static function createDocFinder()
    {
        $schema = (object) [
            'properties' => (object) [
                self::RESOURCE_TYPE => (object) ['type' => 'object']
            ]
        ];
        $docNavigator = Stub::makeEmpty(
            'GoIntegro\\Bundle\\HateoasBundle\\Raml\\DocNavigator',
            ['findRequestSchema' => $schema]
        );
        $ramlDoc = Stub::makeEmpty(
            'GoIntegro\\Bundle\\HateoasBundle\\Raml\\RamlDoc'
        );
        $docFinder = Stub::makeEmpty(
            'GoIntegro\\Bundle\\HateoasBundle\\Raml\\DocFinder',
            ['find' => $ramlDoc, 'createNavigator' => $docNavigator]
        );

        return $docFinder;
    }

    /**
     * @return \GoIntegro\Bundle\HateoasBundle\JsonApi\Request\CreateBodyParser
     */
    private static function createCreationBodyParser()
    {
        return Stub::makeEmpty(
            'GoIntegro\\Bundle\\HateoasBundle\\JsonApi\\Request\\CreateBodyParser',
            ['parse' => [[
                'name' => 'John',
                'surname' => 'Connor'
            ]]]
        );
    }

    /**
     * @return \GoIntegro\Bundle\HateoasBundle\JsonApi\Request\UpdateBodyParser
     */
    private static function createMutationBodyParser()
    {
        return Stub::makeEmpty(
            'GoIntegro\\Bundle\\HateoasBundle\\JsonApi\\Request\\UpdateBodyParser',
            ['parse' => [
                '7' => [
                    'id' => '7',
                    'name' => 'John',
                    'surname' => 'Connor'
                ]
            ]]
        );
    }

    /**
     * @return \GoIntegro\Bundle\HateoasBundle\JsonApi\Request\LinkBodyParser
     */
    private static function createLinkingBodyParser()
    {
        return Stub::makeEmpty(
            'GoIntegro\\Bundle\\HateoasBundle\\JsonApi\\Request\\LinkBodyParser',
            ['parse' => [
                '7' => [
                    'links' => [
                        'user-groups' => []
                    ]
                ]
            ]]
        );
    }

    /**
     * @return \GoIntegro\Bundle\HateoasBundle\JsonApi\Request\UnlinkBodyParser
     */
    private static function createUnlinkingBodyParser()
    {
        return Stub::makeEmpty(
            'GoIntegro\\Bundle\\HateoasBundle\\JsonApi\\Request\\UnlinkBodyParser'
        );
    }

    /**
     * @return \GoIntegro\Bundle\HateoasBundle\JsonApi\Request\TranslationsParser
     */
    private static function createTranslationsParser()
    {
        return Stub::makeEmpty(
            'GoIntegro\\Bundle\\HateoasBundle\\JsonApi\\Request\\TranslationsParser'
        );
    }
}
