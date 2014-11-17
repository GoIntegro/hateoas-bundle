<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\JsonApi\Request;

// HTTP.
use Symfony\Component\HttpFoundation\Request;
// JSON.
use GoIntegro\Bundle\HateoasBundle\Util\JsonCoder;
// RAML.
use GoIntegro\Bundle\HateoasBundle\Raml\DocFinder;

/**
 * @see http://jsonapi.org/format/#crud
 */
class BodyParser
{
    const ERROR_UNSUPPORTED_HTTP_METHOD = "The HTTP method \"%s\" is not supported.";

    /**
     * @param JsonCoder $jsonCoder
     * @param DocFinder $docFinder
     * @param ResourceLinksHydrant $hydrant
     */
    public function __construct(
        JsonCoder $jsonCoder,
        DocFinder $docFinder,
        ResourceLinksHydrant $hydrant
    )
    {
        $this->createBodyParser
            = new CreateBodyParser($jsonCoder, $docFinder, $hydrant);
        $this->updateBodyParser
            = new UpdateBodyParser($jsonCoder, $docFinder, $hydrant);
    }

    /**
     * @param Request $request
     * @param Params $params
     * @return array
     */
    public function parse(Request $request, Params $params)
    {
        switch ($request->getMethod()) {
            case Parser::HTTP_POST:
                return $this->createBodyParser->parse($request, $params);
                break;

            case Parser::HTTP_PUT:
                return $this->updateBodyParser->parse($request, $params);
                break;

            default:
                $message = sprintf(
                    self::ERROR_UNSUPPORTED_HTTP_METHOD,
                    $request->getMethod()
                );
                throw new \ErrorException($message);
        }
    }
}
