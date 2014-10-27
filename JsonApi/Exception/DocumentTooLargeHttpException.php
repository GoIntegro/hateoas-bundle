<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */

namespace GoIntegro\Bundle\HateoasBundle\JsonApi\Exception;

// Exceptions.
use GoIntegro\Bundle\HateoasBundle\Http\RequestEntityTooLargeHttpException;

class DocumentTooLargeHttpException extends RequestEntityTooLargeHttpException
{
    const DEFAULT_MESSAGE = "The response document contains too many resources. Please try requesting a specific page, e.g. \"/resources?page=1\", a specific resource, e.g. \"/resources/27\", or applying some filters, e.g. \"/resources?relationship=11\".";

    /**
     * @param string $message The internal exception message.
     * @param Exception $previous The previous exception.
     * @param integer $code The internal exception code.
     */
    public function __construct(
        $message = self::DEFAULT_MESSAGE,
        Exception $previous = NULL,
        $code = 0
    )
    {
        parent::__construct($message, $previous, $code);
    }
}
