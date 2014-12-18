<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\Http;

// Exceptions.
use Symfony\Component\HttpKernel\Exception\HttpException,
    Exception;

class UnprocessableEntityHttpException extends HttpException
{
    /**
     * @param string $message The internal exception message.
     * @param Exception $previous The previous exception.
     * @param integer $code The internal exception code.
     */
    public function __construct(
        $message = NULL,
        Exception $previous = NULL,
        $code = 0
    )
    {
        parent::__construct(422, $message, $previous, [], $code);
    }
}
