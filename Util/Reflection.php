<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\Util;

// Reflexión.
use ReflectionMethod;

class Reflection
{
    public static function isMethodGetter(ReflectionMethod $method)
    {
        return self::isMethod($method, 'get');
    }

    public static function isMethodInjector(ReflectionMethod $method)
    {
        return self::isMethod($method, 'inject');
    }

    private static function isMethod(ReflectionMethod $method, $prefix)
    {
        return $method->isPublic()
            && !$method->isStatic()
            && 0 === $method->getNumberOfRequiredParameters()
            && $prefix === substr($method->getShortName(), 0, strlen($prefix));
    }
}
