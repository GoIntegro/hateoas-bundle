<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\JsonApi\Merge;

// JSON-API.
use GoIntegro\Bundle\HateoasBundle\JsonApi\Exception\BadRequestException;

class UnmergeableResourcesException extends BadRequestException
{
    const DEFAULT_MESSAGE = "The JSON-API documents cannot be merged. The most likely reason is that the parameters included an individual resource and a resource collection of the same type. E.g. /users and /users/1.";

    /**
     * @param string $message The internal exception message.
     * @param Exception $previous The previous exception.
     * @param integer $code The internal exception code.
     */
    public function __construct(
        $message = self::DEFAULT_MESSAGE,
        $code = 0,
        Exception $previous = NULL
    )
    {
        parent::__construct($message, $code, $previous);
    }

}
