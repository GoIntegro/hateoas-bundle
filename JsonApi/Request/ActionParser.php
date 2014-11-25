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
// JSON-API.
use GoIntegro\Bundle\HateoasBundle\JsonApi\DocumentPagination,
    GoIntegro\Bundle\HateoasBundle\JsonApi\JsonApiSpec;
// JSON.
use GoIntegro\Bundle\HateoasBundle\Util;
// Metadata.
use GoIntegro\Bundle\HateoasBundle\Metadata\Resource\MetadataMinerInterface;

/**
 * @see http://jsonapi.org/format/#introduction
 */
class ActionParser
{
    const ERROR_REQUEST_SCOPE_UNKNOWN = "Could not calculate request scope; whether it affects one or many resources.",
        ERROR_RESOURCE_CONTENT_MISSING = "The primary resource data is missing from the body.";

    /**
     * @var Util\JsonCoder
     */
    protected $jsonCoder;
    /**
     * @var MetadataMinerInterface
     */
    private $mm;

    /**
     * @param Util\JsonCoder $jsonCoder
     * @param MetadataMinerInterface $mm
     */
    public function __construct(
        Util\JsonCoder $jsonCoder,
        MetadataMinerInterface $mm
    )
    {
        $this->jsonCoder = $jsonCoder;
        $this->mm = $mm;
    }

    /**
     * @param Request $request
     * @param Params $params
     * @return array
     */
    public function parse(Request $request, Params $params)
    {
        $action = new RequestAction;

        $action->name = JsonApiSpec::$methodToAction[$request->getMethod()];
        $action->target = !empty($params->relationship)
            ? RequestAction::TARGET_RELATIONSHIP
            : RequestAction::TARGET_RESOURCE;
        $action->type = $this->isMultipleAction($request, $params, $action)
            ? RequestAction::TYPE_MULTIPLE
            : RequestAction::TYPE_SINGLE;

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
            || $this->isPrimaryResourceAList($request, $params, $action)
            || $this->isRelationshipToMany($params, $action);
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

    /**
     * @param Params $params
     * @param RequestAction $action
     * @return boolean
     */
    private function isRelationshipToMany(
        Params $params, RequestAction $action
    )
    {
        if (RequestAction::TARGET_RELATIONSHIP == $action->target) {
            $metadata = $this->mm->mine($params->primaryClass);

            return $metadata->isToManyRelationship($params->relationship);
        }

        return FALSE;
    }
}
