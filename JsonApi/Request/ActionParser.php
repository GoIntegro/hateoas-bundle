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
    /**
     * @var array This mapping is defined by JSON-API, not HTTP nor REST.
     */
    private static $methodToAction = [
        Parser::GET => RequestAction::ACTION_FETCH,
        Parser::POST => RequestAction::ACTION_CREATE,
        Parser::PUT => RequestAction::ACTION_UPDATE,
        Parser::DELETE => RequestAction::ACTION_DELETE
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

        if (
            in_array(
                $action->name,
                [RequestAction::ACTION_FETCH, RequestAction::ACTION_CREATE]
            )
            && !empty($params->primaryIds)
        ) {
            $action->type = 1 < count($params->primaryIds)
                ? RequestAction::TYPE_MULTIPLE
                : RequestAction::TYPE_SINGLE;
        } elseif (RequestAction::ACTION_CREATE && !empty($params->resources)) {
            $action->type = 1 < count($params->resources)
                ? RequestAction::TYPE_MULTIPLE
                : RequestAction::TYPE_SINGLE;
        } elseif (RequestAction::ACTION_UPDATE && !empty($params->entities)) {
            $action->type = 1 < count($params->entities)
                ? RequestAction::TYPE_MULTIPLE
                : RequestAction::TYPE_SINGLE;
        } else {
            throw new ParseException(self::ERROR_REQUEST_SCOPE_UNKNOWN);
        }

        return $action;
    }

    /**
     * @param RequestAction $action
     * @return array
     * @throws ParseException
     */
    private function getCountable(RequestAction $action)
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
