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
use GoIntegro\Bundle\HateoasBundle\JsonApi\Request\Parser;

class ParserTest extends TestCase
{
    const API_BASE_URL = '/api/v1';

    /**
     * @var array
     */
    private static $config = [
        'magic_services' => [
            [
                'resource_type' => 'users',
                'entity_class' => 'Entity\User'
            ]
        ]
    ];

    public function testParsingASimpleRequest()
    {
        // Given...
        $request = self::createRequest(
            '/api/v1/posts/1/linked/likes',
            ['has' => function() { return FALSE; }]
        );
        $parser = new Parser(
            $request,
            self::createFilterParser(),
            self::createPaginationParser(),
            self::createBodyParser(),
            self::API_BASE_URL,
            self::$config
        );
        // When...
        $params = $parser->parse();
        // Then...
        $this->assertEquals('posts', $params->primaryType);
        $this->assertContains('1', $params->primaryIds);
        $this->assertEquals('likes', $params->relationshipType);
    }

    public function testParsingARequestWithSparseFields()
    {
        // Given...
        $has = function($param) { return 'fields' == $param; };
        $get = function() { return 'name,surname,email'; };
        $queryOverrides = ['has' => $has, 'get' => $get];
        $request = self::createRequest('/api/v1/users', $queryOverrides);
        $parser = new Parser(
            $request,
            self::createFilterParser(),
            self::createPaginationParser(),
            self::createBodyParser(),
            self::API_BASE_URL,
            self::$config
        );
        // When...
        $params = $parser->parse();
        // Then...
        $this->assertEquals('users', $params->primaryType);
        $this->assertEmpty($params->primaryIds);
        $this->assertNull($params->relationshipType);
        $this->assertEquals(
            ['users' => ['name', 'surname', 'email']],
            $params->sparseFields
        );
    }

    public function testParsingARequestWithInclude()
    {
        // Given...
        $has = function($param) { return 'include' == $param; };
        $get = function() { return 'platform.account,workspaces-joined'; };
        $queryOverrides = ['has' => $has, 'get' => $get];
        $request = self::createRequest('/api/v1/users', $queryOverrides);
        $parser = new Parser(
            $request,
            self::createFilterParser(),
            self::createPaginationParser(),
            self::createBodyParser(),
            self::API_BASE_URL,
            self::$config
        );
        // When...
        $params = $parser->parse();
        // Then...
        $this->assertEquals('users', $params->primaryType);
        $this->assertEmpty($params->primaryIds);
        $this->assertNull($params->relationshipType);
        $this->assertEquals(
            [['platform', 'account'], ['workspaces-joined']],
            $params->include
        );
    }

    public function testParsingARequestWithSorting()
    {
        // Given...
        $has = function($param) { return 'sort' == $param; };
        $get = function() { return 'surname,name,-registered-date'; };
        $queryOverrides = ['has' => $has, 'get' => $get];
        $request = self::createRequest('/api/v1/users', $queryOverrides);
        $parser = new Parser(
            $request,
            self::createFilterParser(),
            self::createPaginationParser(),
            self::createBodyParser(),
            self::API_BASE_URL,
            self::$config
        );
        // When...
        $params = $parser->parse();
        // Then...
        $this->assertEquals('users', $params->primaryType);
        $this->assertEmpty($params->primaryIds);
        $this->assertNull($params->relationshipType);
        $this->assertEquals(
            ['users' => [
                'surname' => 'ASC',
                'name' => 'ASC',
                'registered-date' => 'DESC'
            ]],
            $params->sorting
        );
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
                'request' => new \stdClass,
                'attributes' => new \stdClass,
                'cookies' => new \stdClass,
                'files' => new \stdClass,
                'server' => new \stdClass,
                'headers' => new \stdClass,
                'query' => $query,
                'getPathInfo' => $pathInfo,
                'getMethod' => $method,
                'getContent' => $body
            ]
        );

        return $request;
    }

    /**
     * @return \GoIntegro\Bundle\HateoasBundle\JsonApi\Request\FilterParser
     */
    private static function createFilterParser()
    {
        return Stub::makeEmpty(
            'GoIntegro\Bundle\HateoasBundle\JsonApi\Request\FilterParser'
        );
    }

    /**
     * @return \GoIntegro\Bundle\HateoasBundle\JsonApi\Request\PaginationParser
     */
    private static function createPaginationParser()
    {
        return Stub::makeEmpty(
            'GoIntegro\Bundle\HateoasBundle\JsonApi\Request\PaginationParser'
        );
    }

    /**
     * @return \GoIntegro\Bundle\HateoasBundle\JsonApi\Request\BodyParser
     */
    private static function createBodyParser()
    {
        return Stub::makeEmpty(
            'GoIntegro\Bundle\HateoasBundle\JsonApi\Request\BodyParser'
        );
    }
}
