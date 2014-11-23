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

/**
 * @see http://jsonapi.org/format/#crud-updating
 */
class RelateBodyParser
{
    const LINKS = 'links';

    const ERROR_EMPTY_BODY = "The resource data was not found on the body.";

    /**
     * @var JsonCoder
     */
    protected $jsonCoder;

    /**
     * @param JsonCoder $jsonCoder
     */
    public function __construct(JsonCoder $jsonCoder)
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

        if (!is_array($data) || !isset($data[$params->relationship])) {
            throw new ParseException(self::ERROR_EMPTY_BODY);
        }

        $entity = reset($params->entities->primary);
        $entityData = [
            (string) $entity->getId() => [
                self::LINKS => [
                    $params->relationship => $data[$params->relationship]
                ]
            ]
        ];

        return $entityData;
    }
}
