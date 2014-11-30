<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\JsonApi\Request;

// HTTP.
use Symfony\Component\HttpFoundation\Request;

interface BodyParserInterface
{
    /**
     * @param Request $request
     * @param Params $params
     * @param array $body
     * @return array
     */
    public function parse(Request $request, Params $params, array $body);
}
