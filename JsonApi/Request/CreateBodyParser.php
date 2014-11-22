<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\JsonApi\Request;

// HTTP.
use Symfony\Component\HttpFoundation\Request;
// Utils.
use GoIntegro\Bundle\HateoasBundle\Util;

/**
 * @see http://jsonapi.org/format/#crud-creating-resources
 */
class CreateBodyParser
{
    // @todo http://jsonapi.org/format/#crud-creating-client-ids
    const ERROR_ID_NOT_SUPPORTED = "Providing an Id on creation is not supported magically yet.";

    /**
     * @var Util\JsonCoder
     */
    protected $jsonCoder;

    /**
     * @param Util\JsonCoder $jsonCoder
     */
    public function __construct(Util\JsonCoder $jsonCoder)
    {
        $this->jsonCoder = $jsonCoder;
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
            throw new ParseException(static::ERROR_PRIMARY_TYPE_KEY);
        } elseif (
            Util\ArrayHelper::isAssociative($data[$params->primaryType])
        ) {
            if (isset($data[$params->primaryType]['id'])) {
                throw new ParseException(static::ERROR_ID_NOT_SUPPORTED);
            } else {
                $entityData[] = $data[$params->primaryType];
            }
        } else {
            foreach ($data[$params->primaryType] as $datum) {
                if (isset($datum['id'])) {
                    throw new ParseException(static::ERROR_ID_NOT_SUPPORTED);
                } else {
                    $entityData[] = $datum;
                }
            }
        }

        return $entityData;
    }
}
