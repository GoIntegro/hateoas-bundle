<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\JsonApi\Request;

// HTTP.
use Symfony\Component\HttpFoundation\Request,
    GoIntegro\Bundle\HateoasBundle\Http\Url;
// Recursos.
use GoIntegro\Bundle\HateoasBundle\JsonApi\DocumentPagination;
// JSON.
use GoIntegro\Bundle\HateoasBundle\Util\JsonCoder;
// RAML.
use GoIntegro\Bundle\HateoasBundle\Raml\DocFinder,
    GoIntegro\Bundle\HateoasBundle\Raml\RamlDoc;

/**
 * @see http://jsonapi.org/format/#crud-updating
 */
class UpdateBodyParser
{
    const ERROR_MISSING_DATA = "No data set found for the resource with the Id \"%s\".",
        ERROR_MISSING_ID = "A data set provided is missing the Id.",
        ERROR_DUPLICATED_ID = "The Id \"%s\" was sent twice.",
        ERROR_PRIMARY_TYPE_KEY = "The resource type key is missing from the body.";

    /**
     * @var JsonCoder
     */
    private $jsonCoder;
    /**
     * @var DocFinder
     */
    private $docFinder;

    /**
     * @param JsonCoder $jsonCoder
     * @param DocFinder $docFinder
     */
    public function __construct(JsonCoder $jsonCoder, DocFinder $docFinder)
    {
        $this->jsonCoder = $jsonCoder;
        $this->docFinder = $docFinder;
    }

    /**
     * @param Request $request
     * @param Params $params
     * @return array
     */
    public function parse(Request $request, Params $params)
    {
        $rawBody = $request->getContent();
        $data = $this->jsonCoder->decode($rawBody);
        $entityData = [];

        if (empty($data[$params->primaryType])) {
            throw new ParseException(self::ERROR_PRIMARY_TYPE_KEY);
        } elseif (isset($data[$params->primaryType]['id'])) {
            $id = $data[$params->primaryType]['id'];

            if (isset($entityData[$id])) {
                $message = sprintf(self::ERROR_DUPLICATED_ID, $id);
                throw new ParseException($message);
            } else {
                $entityData[$id] = $data[$params->primaryType];
            }
        } else {
            foreach ($data[$params->primaryType] as $datum) {
                if (!isset($datum['id'])) {
                    throw new ParseException(self::ERROR_MISSING_ID);
                } else {
                    $entityData[$datum['id']] = $datum;
                }
            }
        }

        $ramlDoc = $this->docFinder->find($params->primaryType);
        $jsonSchema = $this->docFinder
            ->createNavigator($ramlDoc)
            ->findRequestSchema(RamlDoc::HTTP_PUT, $params->primaryType);

        // @todo Move. (To method? To DocNav?)
        $resourceObjectSchema
            = $jsonSchema->properties->{$params->primaryType};

        foreach ($entityData as $data) {
            if (!$this->jsonCoder->matchSchema($data, $resourceObjectSchema)) {
                $message = $this->jsonCoder->getSchemaErrorMessage();
                throw new ParseException($message);
            }
        }

        return $entityData;
    }
}
