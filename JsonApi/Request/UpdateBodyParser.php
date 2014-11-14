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
use GoIntegro\Bundle\HateoasBundle\Raml\DocFinder;

/**
 * @see http://jsonapi.org/format/#fetching
 */
class UpdateBodyParser
{
    const ERROR_MISSING_DATA = "No data set found for the resource with the Id \"%s\".",
        ERROR_MISSING_ID = "A data set provided is missing the Id.";

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

        if (isset($data[$params->primaryType]['id'])) {
            if (isset($entityData[$data[$params->primaryType]['id']])) {

            } elseif (
                (string) $entity->getId()
                    === $data[$params->primaryType]['id']
            ) {
                $entityData[$entity->getId()] = $data[$params->primaryType];
            } else {
                $message = sprintf(self::ERROR_MISSING_DATA, $entity->getId());
                throw new BadRequestHttpException($message);
            }
        } else {
            foreach ($data[$params->primaryType] as $datum) {
                if (!isset($datum['id'])) {
                    throw new BadRequestHttpException(self::ERROR_MISSING_ID);
                } elseif ((string) $entity->getId() === $datum['id']) {
                    $entityData = $datum;
                    break;
                }
            }

            if (empty($entityData)) {
                $message = sprintf(self::ERROR_MISSING_DATA, $entity->getId());
                throw new BadRequestHttpException($message);
            }
        }

        $raml = $this->docFinder->find($params->primaryType);

        if (!$this->jsonCoder->matchSchema($entityData, $raml)) {
            $message = $this->jsonCoder->getSchemaErrorMessage();
            throw new BadRequestHttpException($message);
        }

        return $entityData;
    }
}
