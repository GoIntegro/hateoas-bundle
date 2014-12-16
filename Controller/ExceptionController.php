<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\Controller;

// Symfony.
use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpKernel\Exception\FlattenException,
    Symfony\Component\HttpKernel\Log\DebugLoggerInterface;
// HATEOAS.
use GoIntegro\Bundle\HateoasBundle\Controller\CommonResponseTrait,
    GoIntegro\Hateoas\JsonApi\ErrorObject,
    GoIntegro\Hateoas\JsonApi\ErrorSerializer;

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

    const ERROR_LOG_MESSAGE_PATTERN = "The HATEOAS API is responding with the error \"%s\" in %s:%s. (Occurrence UUID %s.)";

    /**
     * @param Request $request
     * @param FlattenException $exception
     * @param DebugLoggerInterface $logger
     * @return \GoIntegro\Hateoas\Http\JsonResponse
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

        if (500 == $error->status && NULL != $logger) {
            $logger->error(sprintf(
                self::ERROR_LOG_MESSAGE_PATTERN,
                $error->title,
                $exception->getFile(),
                $exception->getLine(),
                $error->id
            ));
        }

        return $this->createNoCacheResponse(
            $serializer->serialize(),
            $error->status,
            $exception->getHeaders()
        );
    }
}
