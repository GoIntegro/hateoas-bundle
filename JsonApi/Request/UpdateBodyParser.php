<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\JsonApi\Request;

// HTTP.
use Symfony\Component\HttpFoundation\Request;

/**
 * @see http://jsonapi.org/format/#crud-updating
 */
class UpdateBodyParser implements BodyParserInterface
{
    const ERROR_MISSING_ID = "A data set provided is missing the Id.",
        ERROR_DUPLICATED_ID = "The Id \"%s\" was sent twice.";

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
        } elseif (isset($body[$params->primaryType]['id'])) {
            $id = $body[$params->primaryType]['id'];

            if (isset($entityData[$id])) {
                $message = sprintf(static::ERROR_DUPLICATED_ID, $id);
                throw new ParseException($message);
            } else {
                $entityData[$id] = $body[$params->primaryType];
            }
        } else {
            foreach ($body[$params->primaryType] as $datum) {
                if (!isset($datum['id'])) {
                    throw new ParseException(static::ERROR_MISSING_ID);
                } else {
                    $entityData[$datum['id']] = $datum;
                }
            }
        }

        return $entityData;
    }
}
