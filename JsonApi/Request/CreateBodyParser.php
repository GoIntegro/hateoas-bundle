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
 * @see http://jsonapi.org/format/#crud-creating-resources
 */
class CreateBodyParser
{
    const ERROR_PRIMARY_TYPE_KEY = "The resource type key is missing from the body.",
        // @todo http://jsonapi.org/format/#crud-creating-client-ids
        ERROR_ID_NOT_SUPPORTED = "Providing an Id on creation is not supported magically yet.";

    /**
     * @var JsonCoder
     */
    private $jsonCoder;
    /**
     * @var DocFinder
     */
    private $docFinder;
    /**
     * @var ResourceLinksHydrant
     */
    private $hydrant;

    /**
     * @param JsonCoder $jsonCoder
     * @param DocFinder $docFinder
     * @param ResourceLinksHydrant $hydrant
     */
    public function __construct(
        JsonCoder $jsonCoder,
        DocFinder $docFinder,
        ResourceLinksHydrant $hydrant
    )
    {
        $this->jsonCoder = $jsonCoder;
        $this->docFinder = $docFinder;
        $this->hydrant = $hydrant;
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
        } elseif ($this->isAssociative($data[$params->primaryType])) {
            if (isset($data[$params->primaryType]['id'])) {
                throw new ParseException(self::ERROR_ID_NOT_SUPPORTED);
            } else {
                $entityData[] = $data[$params->primaryType];
            }
        } else {
            foreach ($data[$params->primaryType] as $datum) {
                if (isset($datum['id'])) {
                    throw new ParseException(self::ERROR_ID_NOT_SUPPORTED);
                } else {
                    $entityData[] = $datum;
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

        foreach ($entityData as &$data) {
            if (!$this->jsonCoder->matchSchema($data, $resourceObjectSchema)) {
                $message = $this->jsonCoder->getSchemaErrorMessage();
                throw new ParseException($message);
            }

            $this->hydrant->hydrate($params, $data);
        }

        return $entityData;
    }

    /**
     * @param array $array
     * @return boolean
     * @todo Move to helper in utils.
     * @see http://stackoverflow.com/a/173479
     */
    private function isAssociative(array $array)
    {
        return array_keys($array) !== range(0, count($array) - 1);
    }
}
