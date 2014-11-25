<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\JsonApi;

/**
 * @see http://jsonapi.org/format/
 */
class JsonApiSpec
{
    /**
     * For the time being, it's the only supported type.
     */
    const HATEOAS_CONTENT_TYPE = 'application/vnd.api+json';

    const JSON_API_SCHEMA_PATH = '@GoIntegroHateoasBundle/Resources/json-schemas/json-api.schema.json';

    /**
     * @var array This mapping is defined by JSON-API, not HTTP nor REST.
     */
    public static $methodToAction = [
        Request\Parser::HTTP_GET => Request\RequestAction::ACTION_FETCH,
        Request\Parser::HTTP_POST => Request\RequestAction::ACTION_CREATE,
        Request\Parser::HTTP_PUT => Request\RequestAction::ACTION_UPDATE,
        Request\Parser::HTTP_DELETE => Request\RequestAction::ACTION_DELETE
    ];

    /**
     * @var array
     * @see http://jsonapi.org/format/#document-structure-resource-object-attributes
     */
    public static $resourceObjectKeys = ['id', 'type', 'href', 'links'];

    /**
     * @var array
     * @todo Actually some are from the extended pagination.
     */
    public static $reservedRequestParams = [
        'include', 'fields', 'sort', 'page', 'size'
    ];

    /**
     * @var array
     * @see http://jsonapi.org/format/#errors
     */
    public static $errorObjectKeys = [
        "id",
        "href",
        "status",
        "code",
        "title",
        "detail",
        "links",
        "path"
    ];
}
