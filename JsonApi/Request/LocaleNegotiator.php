<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\JsonApi\Request;

// HTTP.
use Symfony\Component\HttpFoundation\Request;

class LocaleNegotiator
{
    /**
     * Parsea ciertos parámetros de un pedido de HTTP.
     * @param Request $request
     */
    public function parse(Request $request)
    {
        return substr($request->getPreferredLanguage(), 0, 2);
    }
}
