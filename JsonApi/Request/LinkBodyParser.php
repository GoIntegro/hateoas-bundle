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
class LinkBodyParser implements BodyParserInterface
{
    const LINKS = 'links';

    const ERROR_EMPTY_BODY = "The resource data was not found on the body.",
        ERROR_RELATIONSHIP_TYPE = "The type of the relationship Ids is unexpected",
        ERROR_RELATIONSHIP_EXISTS = "The relationships \"%s\" already exist.",
        ERROR_RELATIONSHIPS_NOT_FOUND = "The relationships \"%s\" were not found.",
        ERROR_RELATIONSHIP_NOT_FOUND = "The relationship was not found.";

    /**
     * @param Request $request
     * @param Params $params
     * @param array $body
     * @return array
     * @todo Sólo sirve para actualizar ahora.
     */
    public function parse(Request $request, Params $params, array $body)
    {
        $entity = reset($params->entities->primary);
        $ids = NULL;

        // @todo Encapsulate.
        $method = 'get' . Util\Inflector::camelize($params->relationship);
        $relation = $entity->$method();

        if ($relation instanceof Collection) {
            $relation = $relation->toArray();
        }

        if (in_array($params->action->name, [
            RequestAction::ACTION_CREATE, RequestAction::ACTION_UPDATE
        ])) {
            if (!is_array($body) || !isset($body[$params->relationship])) {
                throw new ParseException(self::ERROR_EMPTY_BODY);
            }

            $ids = $body[$params->relationship];

            if (RequestAction::ACTION_CREATE == $params->action->name) {
                $ids = $this->parseCreateAction($ids, $relation);
            }
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

    /**
     * @param mixed $ids
     * @param array $relation
     * @return mixed
     * @throws ParseException
     * @throws ExistingRelationshipException
     */
    protected function parseCreateAction($ids, array $relation)
    {
        if (is_array($ids)) {
            if (!is_array($relation)) {
                throw new ParseException(
                    self::ERROR_RELATIONSHIP_TYPE
                );
            }

            $callback = function($entity) {
                return (string) $entity->getId();
            };
            $current = array_map($callback, $relation);
            $intersection = array_intersect($ids, $current);

            if (!empty($intersection)) {
                $existing = implode('", "', $intersection);
                $message = sprintf(self::ERROR_RELATIONSHIP_EXISTS, $existing);
                throw new ExistingRelationshipException($message);
            }

            $ids = array_merge($current, $ids);
        } elseif (is_string($ids) && !empty($relation)) {
            $message = sprintf(self::ERROR_RELATIONSHIP_EXISTS, $ids);
            throw new ExistingRelationshipException($message);
        } elseif (!is_string($ids)) {
            throw new ParseException(self::ERROR_RELATIONSHIP_TYPE);
        }

        return $ids;
    }
}
