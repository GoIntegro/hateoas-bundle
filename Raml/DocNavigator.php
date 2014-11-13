<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\Raml;

// JSON.
use GoIntegro\Bundle\HateoasBundle\Util\JsonCoder;

class DocNavigator
{
    use DereferencesIncludes;

    const ERROR_INVALID_METHOD = "The provided method \"%s\" is invalid.",
        ERROR_INVALID_MEDIA_TYPE = "The provided media type \"%s\" is invalid.",
        ERROR_INVALID_SCHEMA = "The provided schema is not valid.";

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
        $method, $resourceUri, $mediaType = RamlDoc::MEDIA_TYPE_JSON
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

        if (isset(
            $this->ramlDoc->rawRaml[$resourceUri][$method]
            [RamlDoc::REQUEST_BODY][$mediaType][RamlDoc::BODY_SCHEMA]
        )) {
            $schema = $this->ramlDoc->rawRaml[$resourceUri][$method]
                [RamlDoc::REQUEST_BODY][$mediaType][RamlDoc::BODY_SCHEMA];

            if (RamlDoc::isInclude($schema)) {
                $schema = $this->dereferenceInclude(
                    $schema, $this->ramlDoc->fileDir
                );
            } elseif ($this->ramlDoc->hasNamedSchema($schema)) {
                $schema = $this->ramlDoc->getNamedSchema($schema);
            } elseif (!$this->jsonCoder->assertJsonSchema($schema)) {
                throw new \ErrorException(self::ERROR_INVALID_SCHEMA);
            }

            return $schema;
        } else {
            return $this->ramlDoc->getNamedSchema('default');
        }
    }
}
