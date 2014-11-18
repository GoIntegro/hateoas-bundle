<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\JsonApi\Request;

// HTTP.
use Symfony\Component\HttpFoundation\Request;
// JSON.
use GoIntegro\Bundle\HateoasBundle\Util\JsonCoder;
// RAML.
use GoIntegro\Bundle\HateoasBundle\Raml;

/**
 * @see http://jsonapi.org/format/#crud
 */
class BodyParser
{
    const ERROR_UNSUPPORTED_HTTP_METHOD = "The HTTP method \"%s\" is not supported.",
        ERROR_PRIMARY_TYPE_KEY = "The resource type key is missing from the body.",
        ERROR_MISSING_SCHEMA = "A RAML schema was expected for the current action upon the resource \"%s\".",
        ERROR_MALFORMED_SCHEMA = "The RAML schema for the current action is missing the primary type key, \"%s\".";

    /**
     * @var JsonCoder
     */
    protected $jsonCoder;
    /**
     * @var Raml\DocFinder
     */
    protected $docFinder;
    /**
     * @var ResourceLinksHydrant
     */
    protected $hydrant;

    /**
     * @param JsonCoder $jsonCoder
     * @param Raml\DocFinder $docFinder
     * @param ResourceLinksHydrant $hydrant
     */
    public function __construct(
        JsonCoder $jsonCoder,
        Raml\DocFinder $docFinder,
        ResourceLinksHydrant $hydrant
    )
    {
        $this->jsonCoder = $jsonCoder;
        $this->docFinder = $docFinder;
        $this->hydrant = $hydrant;
        $this->createBodyParser = new CreateBodyParser($jsonCoder);
        $this->updateBodyParser
            = new UpdateBodyParser($jsonCoder, $docFinder, $hydrant);
    }

    /**
     * @param Request $request
     * @param Params $params
     * @return array
     */
    public function parse(Request $request, Params $params)
    {
        switch ($request->getMethod()) {
            case Parser::HTTP_POST:
                $data = $this->createBodyParser->parse($request, $params);
                $this->prepareData($params, Raml\RamlDoc::HTTP_POST, $data);
                return $data;

            case Parser::HTTP_PUT:
                $data = $this->updateBodyParser->parse($request, $params);
                $this->prepareData($params, Raml\RamlDoc::HTTP_PUT, $data);
                return $data;

            default:
                $message = sprintf(
                    self::ERROR_UNSUPPORTED_HTTP_METHOD,
                    $request->getMethod()
                );
                throw new \ErrorException($message);
        }
    }

    /**
     * @param Params $params
     * @param string $method
     * @return \stdClass
     * @throws Raml\MissingSchemaException
     * @throws Raml\MalformedSchemaException
     */
    protected function findResourceObjectSchema(Params $params, $method)
    {
        $ramlDoc = $this->docFinder->find($params->primaryType);
        $jsonSchema = $this->docFinder
            ->createNavigator($ramlDoc)
            ->findRequestSchema($method, $params->primaryType);

        if (empty($jsonSchema)) {
            $message = sprintf(
                self::ERROR_MISSING_SCHEMA, $params->primaryType
            );
            throw new Raml\MissingSchemaException($message);
        } elseif (empty($jsonSchema->properties->{$params->primaryType})) {
            $message = sprintf(
                self::ERROR_MALFORMED_SCHEMA, $params->primaryType
            );
            throw new Raml\MalformedSchemaException($message);
        }

        // @todo Move. (To method? To DocNav?)
        return $jsonSchema->properties->{$params->primaryType};
    }

    /**
     * @param Params $params
     * @param string $method
     */
    protected function prepareData(Params $params, $method, array &$entityData)
    {
        $resourceObjectSchema = $this->findResourceObjectSchema(
            $params, $method
        );

        foreach ($entityData as &$data) {
            $json = json_decode(json_encode($data), FALSE);

            if (!$this->jsonCoder->matchSchema($json, $resourceObjectSchema)) {
                $message = $this->jsonCoder->getSchemaErrorMessage();
                throw new ParseException($message);
            }

            $this->hydrant->hydrate($params, $data);
        }
    }
}
