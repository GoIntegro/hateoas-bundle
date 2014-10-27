<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */

namespace GoIntegro\Bundle\HateoasBundle\Collections;

interface Paginated
{
    /**
     * Obtiene el total de items paginados.
     * @return integer
     */
    public function total();
}
