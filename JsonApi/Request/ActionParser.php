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
        $action->type = $this->isMultipleAction($params, $action)
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
     * @return boolean
     * @throws ParseException
     */
    private function isMultipleAction(Params $params, RequestAction $action)
    {
        return $this->isFilteredFetch($params, $action)
            || 1 < count($this->getCountable($params, $action));
    }

    /**
     * @param Params $params
     * @param RequestAction $action
     * @return boolean
     */
    private function isFilteredFetch(Params $params, RequestAction $action)
    {
        return empty($params->primaryIds)
            && RequestAction::ACTION_FETCH == $action->name;
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
                [
                    RequestAction::ACTION_FETCH,
                    RequestAction::ACTION_UPDATE,
                    RequestAction::ACTION_DELETE
                ]
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
