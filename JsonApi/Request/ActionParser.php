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
 * @see http://jsonapi.org/format/#introduction
 */
class ActionParser
{
    const ERROR_REQUEST_SCOPE_UNKNOWN = "Could not calculate request scope; whether it affects one or many resources.";

    /**
     * @var array This mapping is defined by JSON-API, not HTTP nor REST.
     */
    private static $methodToAction = [
        Parser::HTTP_GET => RequestAction::ACTION_FETCH,
        Parser::HTTP_POST => RequestAction::ACTION_CREATE,
        Parser::HTTP_PUT => RequestAction::ACTION_UPDATE,
        Parser::HTTP_DELETE => RequestAction::ACTION_DELETE
    ];

    /**
     * @param Request $request
     * @param Params $params
     * @return array
     */
    public function parse(Request $request, Params $params)
    {
        $action = new RequestAction;

        $action->name = self::$methodToAction[$request->getMethod()];
        $action->type = 1 < count($this->getCountable($params, $action))
            ? RequestAction::TYPE_MULTIPLE
            : RequestAction::TYPE_SINGLE;
        $action->target = !empty($params->relationshipType)
            ? RequestAction::TARGET_RELATIONSHIP
            : RequestAction::TARGET_RESOURCE;

        return $action;
    }

    /**
     * @param Params $params
     * @param RequestAction $action
     * @return array
     * @throws ParseException
     */
    private function getCountable(Params $params, RequestAction $action)
    {
        if (
            in_array(
                $action->name,
                [RequestAction::ACTION_FETCH, RequestAction::ACTION_CREATE]
            )
            && !empty($params->primaryIds)
        ) {
            return $params->primaryIds;
        } elseif (
            RequestAction::ACTION_CREATE === $action->name
            && !empty($params->resources)
        ) {
            return $params->resources;
        } elseif (
            RequestAction::ACTION_UPDATE === $action->name
            && !empty($params->entities)
        ) {
            return $params->entities;
        } else {
            throw new ParseException(self::ERROR_REQUEST_SCOPE_UNKNOWN);
        }
    }
}
