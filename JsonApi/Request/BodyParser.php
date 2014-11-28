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
use GoIntegro\Bundle\HateoasBundle\Util;
// RAML.
use GoIntegro\Bundle\HateoasBundle\Raml;

/**
 * @see http://jsonapi.org/format/#crud
 */
class BodyParser
{
    const ERROR_PRIMARY_TYPE_KEY = "The resource type key is missing from the body.",
        ERROR_MISSING_SCHEMA = "A RAML schema was expected for the current action upon the resource \"%s\".",
        ERROR_MALFORMED_SCHEMA = "The RAML schema for the current action is missing the primary type key, \"%s\".";

    const LINK_SCHEMA = <<<'JSON'
        {
            "type": "object",
            "properties": {
                "links": { "type": "object" }
            }
        }
JSON;

    /**
     * @var Util\JsonCoder
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
     * @var CreateBodyParser
     */
    protected $creationBodyParser;
    /**
     * @var UpdateBodyParser
     */
    protected $mutationBodyParser;
    /**
     * @var RelateBodyParser
     */
    protected $relationBodyParser;

    /**
     * @param Util\JsonCoder $jsonCoder
     * @param Raml\DocFinder $docFinder
     * @param ResourceLinksHydrant $hydrant
     * @param CreateBodyParser $creationBodyParser
     * @param UpdateBodyParser $mutationBodyParser
     * @param RelateBodyParser $relationBodyParser
     * @param TranslationsParser $translationsParser
     */
    public function __construct(
        Util\JsonCoder $jsonCoder,
        Raml\DocFinder $docFinder,
        ResourceLinksHydrant $hydrant,
        CreateBodyParser $creationBodyParser,
        UpdateBodyParser $mutationBodyParser,
        RelateBodyParser $relationBodyParser,
        TranslationsParser $translationsParser
    )
    {
        $this->jsonCoder = $jsonCoder;
        $this->docFinder = $docFinder;
        $this->hydrant = $hydrant;
        $this->creationBodyParser = $creationBodyParser;
        $this->mutationBodyParser = $mutationBodyParser;
        $this->relationBodyParser = $relationBodyParser;
        $this->translationsParser = $translationsParser;
    }

    /**
     * @param Request $request
     * @param Params $params
     * @return array
     */
    public function parse(Request $request, Params $params)
    {
        $data = NULL;
        $schema = NULL;
        $rawBody = $request->getContent();
        $body = $this->jsonCoder->decode($rawBody);

        if (RequestAction::TARGET_RESOURCE == $params->action->target) {
            switch ($params->action->name) {
                case RequestAction::ACTION_CREATE:
                    $data = $this->creationBodyParser->parse(
                        $request, $params, $body
                    );
                    $schema = $this->findResourceObjectSchema(
                        $params, Raml\RamlSpec::HTTP_POST
                    );
                    break;

                case RequestAction::ACTION_UPDATE:
                    $data = $this->mutationBodyParser->parse(
                        $request, $params, $body
                    );
                    $schema = $this->findResourceObjectSchema(
                        $params, Raml\RamlSpec::HTTP_PUT
                    );
                    break;
            }

            if (!empty($body)) {
                $translations = $this->translationsParser->parse(
                    $request, $params, $body
                );

                if (!empty($translations)) {
                    $data['meta'] = [
                        $params->primaryType => [
                            'translations' => $translations
                        ]
                    ];
                }
            }
        } elseif (!RequestAction::ACTION_FETCH != $params->action->name) {
            $data = $this->relationBodyParser->parse(
                $request, $params, $body
            );
            $schema = static::LINK_SCHEMA;
        }

        return !empty($data) && !empty($schema)
            ? $this->prepareData($params, $schema, $data)
            : [];
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
            ->findRequestSchema($method, '/' . $params->primaryType);

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
     * @param \stdClass|string $schema
     * @param array &$entityData
     */
    protected function prepareData(
        Params $params, $schema, array &$entityData
    )
    {
        foreach ($entityData as &$data) {
            $json = Util\ArrayHelper::toObject($data);

            if (!$this->jsonCoder->matchSchema($json, $schema)) {
                $message = $this->jsonCoder->getSchemaErrorMessage();
                throw new ParseException($message);
            }

            $this->hydrant->hydrate($params, $data);
        }

        return $entityData;
    }
}
