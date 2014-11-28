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
     * @param Request $request
     * @param Params $params
     * @param array $body
     * @return array
     */
    public function parse(Request $request, Params $params, array $body)
    {
        $entityData = [];

        if (empty($body[$params->primaryType])) {
            throw new ParseException(BodyParser::ERROR_PRIMARY_TYPE_KEY);
        } elseif (
            Util\ArrayHelper::isAssociative($body[$params->primaryType])
        ) {
            if (isset($body[$params->primaryType]['id'])) {
                throw new ParseException(static::ERROR_ID_NOT_SUPPORTED);
            } else {
                $entityData[] = $body[$params->primaryType];
            }
        } else {
            foreach ($body[$params->primaryType] as $datum) {
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
