<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\JsonApi\Merge;

/**
 * Handles merging two JSON-API documents into one, if possible.
 *
 * Nothing gets overwritten; duplicate keys with different values result in
 * an exception thrown.
 * For non-native speakers, this is not called "merger" because that is
 * a well-known business term.
 * @see http://www.merriam-webster.com/dictionary/merger
 */
class Blender
{
    const ERROR_MISSING_TOP_LEVEL_LINKS = "Por algún motivo desconocido dos recursos del mismo tipo intentan registrar dos plantillas de URL distintas para la misma relación.";

    /**
     * @var array
     */
    private static $reservedTopLevel = ['links', 'linked', 'meta'];

    /**
     * @param array The original JSON-API document.
     * @param array The JSON-API document to merge with the original one.
     * @return array
     */
    public function merge(array $champion, array $challenger)
    {
        $this->mergeTopLevelLinks($champion, $challenger)
            ->mergePrimaryResources($champion, $challenger)
            ->mergeLinkedResources($champion, $challenger)
            ->mergeResourceMeta($champion, $challenger);

        return $champion;
    }

    /**
     * @param array &$champion
     * @param array $challenger
     * @return self
     */
    public function mergeTopLevelLinks(array &$champion, array $challenger)
    {
        static $key = 'links';

        if (isset($champion[$key]) || isset($challenger[$key])) {
            if (empty($champion[$key])) {
                $champion[$key] = $challenger[$key];
            } elseif (!empty($challenger[$key])) {
                $links
                    = array_merge($champion[$key], $challenger[$key]);
                $compare = function(array $linkA, array $linkB) {
                    $comparison = (integer) ($linkA !== $linkB);
                    return $comparison;
                };
                $diff = array_udiff_assoc(
                    $champion[$key], $links, $compare
                );

                if (!empty($diff)) {
                    throw new \LogicException(
                        self::ERROR_MISSING_TOP_LEVEL_LINKS
                    );
                } else {
                    $champion[$key] = $links;
                }
            }
        }

        return $this;
    }

    /**
     * @param array &$champion
     * @param array $challenger
     * @return self
     */
    public function mergePrimaryResources(array &$champion, array $challenger)
    {
        foreach ($challenger as $key => $value) {
            if (in_array($key, static::$reservedTopLevel)) continue;

            if (isset($champion[$key])) {
                $value = $this->resolveSameType($champion[$key], $value);
            }

            $champion[$key] = $value;
        }

        return $this;
    }

    /**
     * @param array &$champion
     * @param array $challenger
     * @return self
     */
    public function mergeLinkedResources(array &$champion, array $challenger)
    {
        static $key = 'linked';

        if (isset($champion[$key]) || isset($challenger[$key])) {
            if (empty($champion[$key])) {
                $champion[$key] = $challenger[$key];
            } elseif (!empty($challenger[$key])) {
                foreach ($challenger[$key] as $type => $collection) {
                    if (isset($champion[$key][$type])) {
                        $champion[$key][$type]
                            = $this->mergeResourceCollections(
                                $champion[$key][$type], $collection
                            );
                    } else {
                        $champion[$key][$type] = $collection;
                    }
                }
            }
        }

        return $this;
    }

    /**
     * @param array &$champion
     * @param array $challenger
     * @return self
     */
    public function mergeResourceMeta(array &$champion, array $challenger)
    {
        static $key = 'meta';

        if (isset($champion[$key]) || isset($challenger[$key])) {
            if (empty($champion[$key])) {
                $champion[$key] = $challenger[$key];
            } elseif (!empty($challenger[$key])) {
                foreach ($challenger[$key] as $type => $meta) {
                    if (empty($champion[$key][$type])) {
                        $champion[$key][$type] = $meta;
                    } else {
                        throw new UnmergeableResourcesException;
                    }
                }
            }
        }

        return $this;
    }

    /**
     * @param array $valueA
     * @param array $valueB
     * @return array
     */
    protected function resolveSameType(array $valueA, array $valueB)
    {
        if (
            static::isResourceObject($valueA)
            && static::isResourceObject($valueB)
        ) {
            return $this->resolveResourceObject($valueA, $valueB);
        } elseif (
            !static::isResourceObject($valueA)
            && !static::isResourceObject($valueB)
        ) {
            return $this->mergeResourceCollections($valueA, $valueB);
        } else {
            throw new UnmergeableResourcesException;
        }
    }

    /**
     * @param array $resourceA
     * @param array $resourceB
     * @return array
     */
    protected function resolveResourceObject(
        array $resourceA, array $resourceB
    )
    {
        if (self::resourcesAreSame($resourceA, $resourceB)) {
            return $this->mergeResourceObjects($resourceA, $resourceB);
        } else {
            throw new UnmergeableResourcesException;
        }
    }

    /**
     * @param array $resourceA
     * @param array $resourceB
     * @return array
     */
    protected function mergeResourceObjects(
        array $resourceA, array $resourceB
    )
    {
        return array_merge_recursive($resourceA, $resourceB);
    }

    /**
     * @param array $collectionA
     * @param array $collectionB
     * @return array
     */
    protected function mergeResourceCollections(
        array $collectionA, array $collectionB
    )
    {
        $byIds = [];

        $callback = function(array $resource) use (&$byIds) {
            $byIds[$resource['id']] = $resource;
        };

        array_walk($collectionA, $callback);
        array_walk($collectionB, $callback);

        return array_values($byIds);
    }

    /**
     * @param array $value
     * @return boolean
     */
    protected static function isResourceObject(array $value)
    {
        return isset($value['id']); // That simple.
    }

    /**
     * @param array $resourceA
     * @param array $resourceB
     * @return boolean
     */
    protected static function resourcesAreSame(
        array $resourceA, array $resourceB
    )
    {
        return $resourceA['id'] === $resourceB['id'];
    }
}
