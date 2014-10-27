<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\Collections;

// Datos.
use Closure;

interface Moonwalker
{
    /**
     * @param Closure $func
     * @return boolean
     */
    public function walk(Closure $func);
}
