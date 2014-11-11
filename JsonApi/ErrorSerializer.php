<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\JsonApi;

/**
 * @see http://jsonapi.org/format/#errors
 */
class ErrorSerializer
{
    /**
     * @var ErrorObject
     */
    private $error;

    /**
     * @param ErrorObject $exception
     */
    public function __construct(ErrorObject $error)
    {
        $this->error = $error;
    }

    public function serialize()
    {
        $json = [];

        foreach ($this->error as $field => $value) {
            if (NULL === $value || ('code' == $field && 0 == $value)) continue;

            $json[$field] = $value;
        }

        return ['errors' => [$json]];
    }
}
