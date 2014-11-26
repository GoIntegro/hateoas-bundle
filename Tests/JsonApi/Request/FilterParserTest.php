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

class FilterParserTest extends TestCase
{
    const API_BASE_URL = '/api/v1',
        HTTP_PUT_BODY = <<<'JSON'
{
    "users": {
        "id": "7",
        "name": "John",
        "surname": "Connor"
    }
}
JSON;

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

    public function testParsingARequestWithFilters()
    {
        $this->markTestIncomplete("TODO.");
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
        $this->miner = Stub::makeEmpty(
            'GoIntegro\Bundle\HateoasBundle\Metadata\Resource\MetadataMinerInterface'
        );
    }
}
