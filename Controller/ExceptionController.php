<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\Controller;

// Symfony.
use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\Debug\Exception\FlattenException,
    Symfony\Component\HttpKernel\Log\DebugLoggerInterface;
// HATEOAS.
use GoIntegro\Bundle\HateoasBundle\Controller\CommonResponseTrait,
    GoIntegro\Bundle\HateoasBundle\JsonApi\ErrorObject,
    GoIntegro\Bundle\HateoasBundle\JsonApi\ErrorSerializer;

/**
 * Add these lines to your Symfony 2 configuration.
 * # app/config/config.yml
 * twig:
 *   exception_controller: 'GoIntegro\Bundle\HateoasBundle\Controller\ExceptionController::showAction'
 * @see Symfony\Bundle\TwigBundle\Controller\ExceptionController
 */
class ExceptionController
{
    use CommonResponseTrait;

    /**
     * Converts an Exception to a Response.
     *
     * @param Request              $request   The request
     * @param FlattenException     $exception A FlattenException instance
     * @param DebugLoggerInterface $logger    A DebugLoggerInterface instance
     *
     * @return Response
     *
     * @throws \InvalidArgumentException When the exception template does not exist
     */
    public function showAction(
        Request $request,
        FlattenException $exception,
        DebugLoggerInterface $logger = NULL
    )
    {
        $error = new ErrorObject;
        $error->status = $exception->getStatusCode();
        $error->title = $exception->getMessage();
        $error->code = $exception->getCode();
        $serializer = new ErrorSerializer($error);

        return $this->createNoCacheResponse(
            $serializer->serialize(),
            $error->status,
            $exception->getHeaders()
        );
    }
}
