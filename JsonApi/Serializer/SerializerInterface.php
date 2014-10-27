<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */

namespace GoIntegro\Bundle\HateoasBundle\JsonApi\Serializer;

interface SerializerInterface
{
    /**
     * Retorna una estructura de datos serializable.
     * @return array
     */
    public function serialize();
}
