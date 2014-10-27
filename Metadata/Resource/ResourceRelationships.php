<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\Metadata\Resource;

// Iteración.
use IteratorAggregate, ArrayIterator, Countable;
// Colecciones.
use GoIntegro\Bundle\HateoasBundle\Collections\Moonwalker;
// Datos.
use Closure;

/**
 * Las relaciones de un recurso.
 */
class ResourceRelationships implements IteratorAggregate, Countable, Moonwalker
{
    /**
     * @var array
     */
    public static $kinds = [
        ResourceRelationship::TO_ONE,
        ResourceRelationship::TO_MANY,
        ResourceRelationship::LINK_ONLY
    ];
    /**
     * @var array
     */
    private static $idRelationshipKinds = [
        ResourceRelationship::TO_ONE,
        ResourceRelationship::TO_MANY
    ];
    /**
     * @var array
     */
    public $toOne = [];
    /**
     * @var array
     */
    public $toMany = [];
    /**
     * @var array
     */
    public $linkOnly = [];
    /**
     * @var array
     */
    public $dbOnly = [];

    /**
     * @param array $blacklist
     * @param array $linkOnly
     * @return self
     */
    public function clean(array $blacklist, $linkOnly)
    {
        $blacklist = array_flip($blacklist);

        // Se usa "kind" y no "type" para no confundir con sus otros usos.
        foreach (self::$kinds as $kind) {
            $this->$kind = array_diff_key($this->$kind, $blacklist);
        }

        foreach ($linkOnly as $relationship) {
            foreach (self::$idRelationshipKinds as $kind) {
                $relationships = &$this->$kind;
                if (isset($relationships[$relationship])) {
                    $this->linkOnly[$relationship]
                        = $relationships[$relationship];
                    unset($relationships[$relationship]);
                }
            }
        }

        return $this;
    }

    /**
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->all());
    }

    /**
     * @see Moonwalker::walk
     */
    public function walk(Closure $func)
    {
        return array_walk($this->toOne, $func)
            && array_walk($this->toMany, $func)
            && array_walk($this->linkOnly, $func);
    }

    /**
     * @see Countable::count
     */
    public function count()
    {
        return count($this->all());
    }

    /**
     * @return array
     */
    public function all()
    {
        return array_merge($this->toOne, $this->toMany, $this->linkOnly);
    }
}
