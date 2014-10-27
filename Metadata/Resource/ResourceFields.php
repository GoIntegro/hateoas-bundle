<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */

namespace GoIntegro\Bundle\HateoasBundle\Metadata\Resource;

// Iteración.
use IteratorAggregate, ArrayIterator, Countable;

/**
 * Los campos de un recurso.
 */
class ResourceFields implements IteratorAggregate, Countable
{
    /**
     * @var array
     */
    public $original = [];
    /**
     * @var array
     */
    public $injected = [];

    /**
     * @param array $original
     * @param array $injected
     */
    public function __construct(array $original, array $injected = [])
    {
        $this->original = $original;
        $this->injected = $injected;
    }

    /**
     * @param array $original
     * @param self
     */
    public function reset(array $original)
    {
        $this->original = $original;
        $this->injected = [];

        return $this;
    }

    /**
     * @param array $blacklist
     * @return self
     */
    public function clean(array $blacklist)
    {
        foreach (['original', 'injected'] as $kind) {
            $this->$kind = array_diff($this->$kind, $blacklist);
        }

        return $this;
    }

    /**
     * @param array $fields
     * @return self
     */
    public function inject(array $fields)
    {
        $this->injected = array_merge($this->injected, $fields);

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
        return array_merge($this->original, $this->injected);
    }
}
