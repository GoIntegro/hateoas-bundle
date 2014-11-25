<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\JsonApi;

/**
 * @see http://jsonapi.org/format/
 */
class JsonApiSpec
{
    /**
     * @var array
     */
    private static $reserved = ['include', 'fields', 'sort', 'page', 'size'];
}
