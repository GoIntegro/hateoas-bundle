<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\Raml;

// JSON.
use GoIntegro\Bundle\HateoasBundle\Util\JsonCoder;

/**
 * Uses JSON-API assumptions to navigate a RAML.
 */
class DocNavigator
{
    use DereferencesIncludes;

    const ERROR_INVALID_METHOD = "The provided method \"%s\" is invalid.",
        ERROR_INVALID_MEDIA_TYPE = "The provided media type \"%s\" is invalid.",
        ERROR_INVALID_SCHEMA = "The provided schema is not valid.",
        ERROR_INVALID_KEY = "One of the keys provided to navigate the RAML is not valid.",
        ERROR_KEY_NOT_FOUND = "A path for one of the keys provided \"%s\" was not found.",
        ERROR_PARAM_NOT_FOUND = "One of the keys provided \"%s\" was assumed to be an Id or list of Ids, but a matching path was not found.";

    /**
     * @var RamlDoc
     */
    private $ramlDoc;
    /**
     * @var JsonCoder
     */
    private $jsonCoder;

    /**
     * @param RamlDoc $ramlDoc
     * @param JsonCoder $jsonCoder
     */
    public function __construct(RamlDoc $ramlDoc, JsonCoder $jsonCoder)
    {
        $this->ramlDoc = $ramlDoc;
        $this->jsonCoder = $jsonCoder;
    }

    /**
     * @param string $method
     * @param string $resourceType
     * @param string $mediaType
     * @return \stdClass|NULL
     */
    public function findRequestSchema(
        $method, $resourceUri, $mediaType = RamlSpec::MEDIA_TYPE_JSON
    )
    {
        if (!RamlDoc::isValidMethod($method)) {
            $message = sprintf(self::ERROR_INVALID_METHOD, $method);
            throw new \UnexpectedValueException($message);
        }

        if (!RamlDoc::isValidMediaType($mediaType)) {
            $message = sprintf(self::ERROR_INVALID_MEDIA_TYPE, $mediaType);
            throw new \UnexpectedValueException($mediaType);
        }

        try {
            $schema = $this->navigate(
                $resourceUri,
                $method,
                RamlSpec::REQUEST_BODY,
                $mediaType,
                RamlSpec::BODY_SCHEMA
            );
        } catch (PathNotFoundException $e) {
            // In this case, this is OK. Return values are based on navigation,
            // any value may be found and thus is valid, hence return values
            // can't be used to express "not found".
        }

        if (isset($schema)) {
            if (RamlDoc::isInclude($schema)) {
                $schema = $this->dereferenceInclude(
                    $schema, $this->ramlDoc->fileDir
                );
            } elseif ($this->ramlDoc->hasNamedSchema($schema)) {
                $schema = $this->ramlDoc->getNamedSchema($schema);
            } elseif (!$this->jsonCoder->assertJsonSchema($schema)) {
                throw new MalformedSchemaException(self::ERROR_INVALID_SCHEMA);
            }

            return $schema;
        } else {
            list($resourceType) = explode('/', substr($resourceUri, 1));

            return $this->ramlDoc->getNamedSchema($resourceType);
        }
    }

    /**
     * @param string $key Any number of "key" args can be passed.
     * @return mixed
     * @throws \ErrorException
     */
    public function navigate()
    {
        $args = array_merge([$this->ramlDoc->rawRaml], func_get_args());

        return call_user_func_array('self::dig', $args);
    }

    /**
     * @param mixed $raml
     * @param string $key
     * @return mixed
     * @throws \ErrorException
     * @throws PathNotFoundException
     */
    private static function dig($raml, $key = NULL)
    {
        $args = array_slice(func_get_args(), 2);

        if (!empty($key)) {
            if (!is_scalar($key)) {
                throw new \ErrorException(self::ERROR_INVALID_KEY);
            } elseif (RamlDoc::isResource($key)) {
                $parts = explode('/', substr($key, 1));

                if (1 < count($parts)) {
                    $callback = function($part) { return '/' . $part; };
                    $parts = array_map($callback, $parts);
                    $parts = array_merge([$raml], $parts);
                    $raml = call_user_func_array(__METHOD__, $parts);
                    $key = array_shift($args);
                } elseif (self::isIdList($key)) { // @todo Support non-num Ids.
                    $found = FALSE;

                    foreach (array_keys($raml) as $property) {
                        if (RamlDoc::isParameter($property)) {
                            $found = TRUE;
                            $key = $property;
                            break;
                        }
                    }

                    if (!$found) {
                        $message = sprintf(self::ERROR_PARAM_NOT_FOUND, $key);
                        throw new PathNotFoundException($message);
                    }
                }
            }

            if (!isset($raml[$key])) {
                $message = sprintf(self::ERROR_KEY_NOT_FOUND, $key);
                throw new PathNotFoundException($message);
            }

            $args = array_merge([$raml[$key]], $args);
            $raml = call_user_func_array(__METHOD__, $args);
        }

        return $raml;
    }

    /**
     * @param string $value
     * @return boolean
     */
    private static function isIdList($value)
    {
        return 1 === preg_match('/^\/[0-9]+[,0-9]*$/', $value);
    }
}
