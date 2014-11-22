<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\Util;

class ArrayHelper
{
    /**
     * @param array $array
     * @return boolean
     * @todo Move to helper in utils.
     * @see http://stackoverflow.com/a/173479
     */
    public static function isAssociative(array $array)
    {
        return array_keys($array) !== range(0, count($array) - 1);
    }

    /**
     * Something like recursive casting, only cheaper.
     * @param array $array
     * @return \stdClass
     */
    public static function toObject(array $array)
    {
        return json_decode(json_encode($array), FALSE);
    }
}
