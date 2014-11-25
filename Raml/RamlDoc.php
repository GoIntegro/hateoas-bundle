<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\Raml;

class RamlDoc
{
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
     * @param array $rawRaml
     * @param string $fileName
     */
    public function __construct(array $rawRaml, $fileName)
    {
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
     * @param string $method
     * @return boolean
     */
    public static function isValidMethod($method)
    {
        return in_array($method, RamlSpec::$methods);
    }

    /**
     * @param string $mediaType
     * @return boolean
     */
    public static function isValidMediaType($mediaType)
    {
        return in_array($mediaType, RamlSpec::$mediaTypes);
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

    /**
     * @param string $value
     * @return boolean
     */
    public static function isParameter($value)
    {
        return self::isResource($value)
            && '{' === substr($value, 1, 1) && '}' === substr($value, -1);
    }

    /**
     * @param string $method
     * @param string $path
     * @return boolean
     */
    public function isDefined($method, $path)
    {
        $raml = $this->getPathDefinition($path);

        return isset($raml[$method]);
    }

    /**
     * @param string $path
     * @param integer $case
     * @return array
     */
    public function getAllowedMethods($path, $case = CASE_LOWER)
    {
        $raml = $this->getPathDefinition($path);
        $methods = array_intersect(array_keys($raml), RamlSpec::$methods);

        if (CASE_UPPER === $case) {
            $callback = function($method) { return strtoupper($method); };
            $methods = array_map($callback, $methods);
        }

        return array_values($methods);
    }

    /**
     * @param string $path
     * @return array|NULL
     */
    public function getPathDefinition($path)
    {
        $raml = $this->rawRaml;

        foreach (explode('/', substr($path, 1)) as $part) {
            $resource = '/' . $part;

            if (isset($raml[$resource])) {
                $raml = $raml[$resource];
            } else {
                foreach (array_keys($raml) as $key) {
                    if (static::isParameter($key)) {
                        $raml = $raml[$key];
                        break;
                    }
                }
            }
        }

        return $raml;
    }
}
