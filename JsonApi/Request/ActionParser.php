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
use GoIntegro\Bundle\HateoasBundle\Util;

/**
 * @see http://jsonapi.org/format/#introduction
 */
class ActionParser
{
    const ERROR_REQUEST_SCOPE_UNKNOWN = "Could not calculate request scope; whether it affects one or many resources.",
        ERROR_RESOURCE_CONTENT_MISSING = "The primary resource data is missing from the body.";

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
        $action = new RequestAction;

        $action->name = self::$methodToAction[$request->getMethod()];
        $action->type = $this->isMultipleAction($request, $params, $action)
            ? RequestAction::TYPE_MULTIPLE
            : RequestAction::TYPE_SINGLE;
        $action->target = !empty($params->relationship)
            ? RequestAction::TARGET_RELATIONSHIP
            : RequestAction::TARGET_RESOURCE;

        return $action;
    }

    /**
     * @param Request $request
     * @param Params $params
     * @param RequestAction $action
     * @return boolean
     * @throws ParseException
     */
    private function isMultipleAction(
        Request $request, Params $params, RequestAction $action)
    {
        return $this->isFilteredFetch($params, $action)
            || $this->isIdParamAList($params, $action)
            || $this->isPrimaryResourceAList($request, $params, $action);
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
     * @return boolean
     */
    private function isIdParamAList(Params $params, RequestAction $action)
    {
        return in_array(
                $action->name,
                [
                    RequestAction::ACTION_FETCH,
                    RequestAction::ACTION_UPDATE,
                    RequestAction::ACTION_DELETE
                ]
            )
            && 1 < count($params->primaryIds);
    }

    /**
     * @param Request $request
     * @param Params $params
     * @param RequestAction $action
     * @return boolean
     * @throws ParseException
     */
    private function isPrimaryResourceAList(
        Request $request, Params $params, RequestAction $action
    )
    {
        $json = $request->getContent();

        if (
            RequestAction::TARGET_RESOURCE == $action->target
            && in_array($action->name, [
                RequestAction::ACTION_CREATE, RequestAction::ACTION_UPDATE
            ])
        ) {
            $data = $this->jsonCoder->decode($json);

            if (!is_array($data) || !isset($data[$params->primaryType])) {
                throw new ParseException(self::ERROR_RESOURCE_CONTENT_MISSING);
            }

            return !Util\ArrayHelper::isAssociative(
                $data[$params->primaryType]
            );
        }


        return FALSE;
    }
}
