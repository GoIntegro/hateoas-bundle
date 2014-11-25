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
use GoIntegro\Bundle\HateoasBundle\Util;
// Collections.
use Doctrine\Common\Collections\Collection;

/**
 * @see http://jsonapi.org/format/#crud-updating
 */
class RelateBodyParser
{
    const LINKS = 'links';

    const ERROR_EMPTY_BODY = "The resource data was not found on the body.",
        ERROR_RELATIONSHIP_TYPE = "The type of the relationship Ids is unexpected",
        ERROR_RELATIONSHIP_EXISTS = "The relationships \"%s\" already exist.";

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
     * @todo Sólo sirve para actualizar ahora.
     */
    public function parse(Request $request, Params $params)
    {
        $entity = reset($params->entities->primary);
        $ids = NULL;

        $method = 'get' . Util\Inflector::camelize($params->relationship);
        $relation = $entity->$method();

        if (in_array($params->action->name, [
            RequestAction::ACTION_CREATE, RequestAction::ACTION_UPDATE
        ])) {
            $rawBody = $request->getContent();
            $data = $this->jsonCoder->decode($rawBody);

            if (!is_array($data) || !isset($data[$params->relationship])) {
                throw new ParseException(self::ERROR_EMPTY_BODY);
            }

            $ids = $data[$params->relationship];

            if (RequestAction::ACTION_CREATE == $params->action->name) {
                if (is_array($ids)) {
                    if ($relation instanceof Collection) {
                        $relation = $relation->toArray();
                    }

                    if (!is_array($relation)) {
                        throw new ParseException(
                            self::ERROR_RELATIONSHIP_TYPE
                        );
                    }

                    $callback = function($entity) { return $entity->getId(); };
                    $list = array_map($callback, $relation);
                    $intersection = array_intersect($ids, $list);

                    if (!empty($intersection)) {
                        $message = sprintf(
                            self::ERROR_RELATIONSHIP_EXISTS,
                            implode('", "', $ids)
                        );
                        throw new ExistingRelationshipException($message);
                    }
                } elseif (is_string($ids) && !empty($relation)) {
                    $message = sprintf(self::ERROR_RELATIONSHIP_EXISTS, $ids);
                    throw new ExistingRelationshipException($message);
                } else {
                    throw new ParseException(self::ERROR_RELATIONSHIP_TYPE);
                }
            }
        } elseif (RequestAction::ACTION_DELETE == $params->action->name) {
            //
        }

        $entityData = [
            (string) $entity->getId() => [
                self::LINKS => [
                    $params->relationship => $ids
                ]
            ]
        ];

        return $entityData;
    }
}
