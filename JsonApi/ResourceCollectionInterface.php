<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\JsonApi;

// Arreglos.
use Countable, Traversable, ArrayAccess;
// Datos.
use Closure;

interface ResourceCollectionInterface
    extends ResourceDocument, Countable, Traversable, ArrayAccess
{
    /**
     * @param Closure $func
     * @return self
     */
    public function map(Closure $func);
}
