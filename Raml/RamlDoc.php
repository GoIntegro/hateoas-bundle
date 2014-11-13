<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\Raml;

// RAML.
use Raml\ApiDefinition;

class RamlDoc
{
    const HTTP_OPTIONS = 'options',
        HTTP_HEAD = 'head',
        HTTP_GET = 'get',
        HTTP_POST = 'post',
        HTTP_PUT = 'put',
        HTTP_DELETE = 'delete',
        HTTP_PATCH = 'patch';

    const MEDIA_TYPE_JSON = 'application/json',
        MEDIA_TYPE_XML = 'text/xml';

    const REQUEST_BODY = 'body',
        BODY_SCHEMA = 'schema';

    /**
     * @var array
     */
    private static $methods = [
        self::HTTP_OPTIONS,
        self::HTTP_HEAD,
        self::HTTP_GET,
        self::HTTP_POST,
        self::HTTP_PUT,
        self::HTTP_DELETE,
        self::HTTP_PATCH
    ];
    /**
     * @var array
     */
    private static $mediaTypes = [
        self::MEDIA_TYPE_JSON,
        self::MEDIA_TYPE_XML
    ];
    /**
     * @var ApiDefinition
     */
    public $apiDef;
    /**
     * @var array Read-only.
     */
    public $rawRaml;
    /**
     * @var string
     */
    public $fileName;
    /**
     * @var string
     */
    public $fileDir;
    /**
     * @var array
     */
    private $schemaMaps = [];

    /**
     * @param $apiDef
     * @param array $rawRaml
     * @param string $fileName
     */
    public function __construct(
        ApiDefinition $apiDef, array $rawRaml, $fileName
    )
    {
        $this->apiDef = $apiDef;
        $this->rawRaml = $rawRaml;
        $this->fileName = $fileName;
        $this->fileDir = dirname($fileName);
    }

    /**
     * @param array $map
     * @return self
     */
    public function addSchemaMap(array $map)
    {
        $this->schemaMaps[] = $map;
    }

    /**
     * @param string $name
     * @return \stdClass|NULL
     */
    public function hasNamedSchema($name)
    {
        foreach ($this->schemaMaps as $map) {
            foreach ($map as $key => $schema) {
                if ($key === $name) return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * @param string $name
     * @return \stdClass|NULL
     */
    public function getNamedSchema($name)
    {
        foreach ($this->schemaMaps as $map) {
            foreach ($map as $key => $schema) {
                if ($key === $name) return $schema;
            }
        }

        return NULL;
    }

    /**
     * @param string $name
     * @param array $args
     */
    public function __call($name, $args)
    {
        $method = [$this->apiDef, $name];

        if (is_callable($method)) {
            return call_user_func_array($method, $args);
        } else {
            throw \BadMethodCallException("No such method here.");
        }
    }

    /**
     * @param string $method
     * @return boolean
     */
    public static function isValidMethod($method)
    {
        return in_array($method, self::$methods);
    }

    /**
     * @param string $mediaType
     * @return boolean
     */
    public static function isValidMediaType($mediaType)
    {
        return in_array($mediaType, self::$mediaTypes);
    }

    /**
     * @param string $value
     * @return boolean
     */
    public static function isInclude($value)
    {
        return 0 === strpos($value, '!include ');
    }

    /**
     * @param string $value
     * @return boolean
     */
    public static function isResource($value)
    {
        return 0 === strpos($value, '/');
    }
}
