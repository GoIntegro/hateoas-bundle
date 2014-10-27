<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */

namespace GoIntegro\Bundle\HateoasBundle\JsonApi\Serializer;

// Serialization.
use JsonSerializable;
// Structures.
use stdClass;
// Time.
use DateTime;
// Excepciones.
use InvalidArgumentException;

class ObjectFieldValueSerializer implements SerializerInterface
{
    const ISO_8601_COMPLETE = 'c',
        ERROR_OBJECT_EXPECTED = 'Sólo puedo serializar objetos.';

    /**
     * @var object
     */
    private $fieldValue;

    public function __construct($fieldValue)
    {
        if (!is_object($fieldValue)) {
            throw new InvalidArgumentException(self::ERROR_OBJECT_EXPECTED);
        }

        $this->fieldValue = $fieldValue;
    }

    public function serialize()
    {
        return $this->serializeObject($this->fieldValue);
    }

    private function serializeObject($object)
    {
        $json = NULL;

        if ($object instanceof JsonSerializable) {
            $json = $object; // json_encode() will be called later.
        } elseif ($object instanceof DateTime) {
            $json = $object->format(self::ISO_8601_COMPLETE);
        } elseif ($object instanceof stdClass) {
            foreach ($object as $value) {
                if (is_object($value)) {
                    $this->serializeObject($value); // Recursion.
                } else {
                   $json = $value;
                }
            }
        }

        return $json;
    }
}
