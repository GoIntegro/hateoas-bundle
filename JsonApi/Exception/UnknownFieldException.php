<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Tito Costa <titomiguelcosta@gmail.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\JsonApi\Exception;

class UnknownFieldException extends BadRequestException
{
    private $field;
    private $resource;

    public function __construct($field, $resource, $message = "")
    {
        parent::__construct($message);

        $this->field = $field;
        $this->resource = $resource;
    }

    public function getField()
    {
        return $this->field;
    }

    public function getResource()
    {
        return $this->resource;
    }
}
