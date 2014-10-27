<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\JsonApi;

/**
 * Interfaz común para los recursos REST.
 */
interface ResourceEntityInterface
{
    /**
     * @return integer|string
     */
    public function getId();
}
