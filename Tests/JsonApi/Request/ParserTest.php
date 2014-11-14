<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace JsonApi\Request;

// Mocks.
use Codeception\Util\Stub;
// Request.
use GoIntegro\Bundle\HateoasBundle\JsonApi\Request\Parser;
// Tests.
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class ParserTest extends TestCase
{
    const API_BASE_URL = '/api/v1';

    /**
     * @var \Parser
     */
    protected $miner;

    protected function setUp()
    {
        $this->miner = Stub::makeEmpty(
            'GoIntegro\Bundle\HateoasBundle\Metadata\Resource\MetadataMinerInterface'
        );
    }

    protected function tearDown()
    {
        unset($this->miner);
    }

    public function testParsingASimpleRequest()
    {
        // Given...
        $request = self::createRequest(
            '/api/v1/posts/1/linked/likes',
            ['has' => function() { return FALSE; }]
        );
        $parser = new Parser($this->miner, $request, self::API_BASE_URL, []);
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
        $parser = new Parser($this->miner, $request, self::API_BASE_URL, []);
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
        $parser = new Parser($this->miner, $request, self::API_BASE_URL, []);
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
        $parser = new Parser($this->miner, $request, self::API_BASE_URL, []);
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

    public function testParsingARequestWithPagination()
    {
        // Given...
        $has = function($param) { return in_array($param, ['page', 'size']); };
        $get = function($param) { return 'page' == $param ? 2 : 4; };
        $queryOverrides = ['has' => $has, 'get' => $get];
        $request = self::createRequest('/api/v1/users', $queryOverrides);
        $config = [
            'magic_services' => [
                [
                    'resource_type' => 'users',
                    'entity_class' => 'Entity\User'
                ]
            ]
        ];
        $parser
            = new Parser($this->miner, $request, self::API_BASE_URL, $config);
        // When...
        $params = $parser->parse();
        // Then...
        $this->assertEquals('users', $params->primaryType);
        $this->assertEmpty($params->primaryIds);
        $this->assertNull($params->relationshipType);
        $this->assertNotNull($params->pagination);
        $this->assertNull($params->pagination->total);
        $this->assertEquals(2, $params->pagination->page);
        $this->assertEquals(4, $params->pagination->size);
        $this->assertEquals(4, $params->pagination->offset);
        $this->assertInstanceOf(
            'GoIntegro\Bundle\HateoasBundle\Http\Url',
            $params->pagination->paginationlessUrl
        );
    }

    public function testParsingARequestWithAnUpdateBody()
    {
        // Given...
        $queryOverrides = [
            'getContent' => function() { return self::UPDATE_BODY; }
        ];
        $request = self::createRequest('/api/v1/users', $queryOverrides);
        $config = [
            'magic_services' => [
                [
                    'resource_type' => 'users',
                    'entity_class' => 'Entity\User'
                ]
            ]
        ];
        $parser
            = new Parser($this->miner, $request, self::API_BASE_URL, $config);
        // When...
        $params = $parser->parse();
        // Then...
        $this->assertEquals('users', $params->primaryType);
        $this->assertEquals('users', $params->primaryType);
    }

    /**
     * @param string $pathInfo
     * @param array $queryOverrides
     * @return Request
     */
    private static function createRequest($pathInfo, array $queryOverrides)
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
            ['query' => $query, 'getPathInfo' => $pathInfo]
        );

        return $request;
    }
}
