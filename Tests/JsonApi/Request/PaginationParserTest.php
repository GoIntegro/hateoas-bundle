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
use GoIntegro\Bundle\HateoasBundle\JsonApi\Request\PaginationParser,
    GoIntegro\Bundle\HateoasBundle\JsonApi\Request\Parser;

class PaginationParserTest extends TestCase
{
    const RESOURCE_CLASS = 'HateoasInc\\Bundle\\ExampleBundle\\Entity\\User';

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

    public function testParsingARequestWithPagination()
    {
        // Given...
        $has = function($param) { return in_array($param, ['page', 'size']); };
        $get = function($param) { return 'page' == $param ? 2 : 4; };
        $queryOverrides = ['has' => $has, 'get' => $get];
        $request = self::createRequest('/api/v1/users', $queryOverrides);
        $params = Stub::makeEmpty(
            'GoIntegro\\Bundle\\HateoasBundle\\JsonApi\\Request\\Params',
            ['primaryClass' => self::RESOURCE_CLASS]
        );
        $parser = new PaginationParser(
            self::createMetadataMiner(),
            self::$config
        );
        // When...
        $pagination = $parser->parse($request, $params);
        // Then...
        $this->assertNotNull($pagination);
        $this->assertNull($pagination->total);
        $this->assertEquals(2, $pagination->page);
        $this->assertEquals(4, $pagination->size);
        $this->assertEquals(4, $pagination->offset);
        $this->assertInstanceOf(
            'GoIntegro\Bundle\HateoasBundle\Http\Url',
            $pagination->paginationlessUrl
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
                'query' => $query,
                'getPathInfo' => $pathInfo,
                'getMethod' => $method,
                'getContent' => $body
            ]
        );

        return $request;
    }

    private static function createMetadataMiner()
    {
        return Stub::makeEmpty(
            'GoIntegro\Bundle\HateoasBundle\Metadata\Resource\MetadataMinerInterface'
        );
    }
}
