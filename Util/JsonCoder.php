<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\Util;

// JSON.
use JsonSchema\Validator;

/**
 * La fachada del servicio de validación de JSON schemas.
 */
class JsonCoder
{
    /**
     * @var array
     */
    private static $errorMessages = [
        JSON_ERROR_NONE => "No error has occurred.",
        JSON_ERROR_DEPTH => "The maximum stack depth has been exceeded.",
        JSON_ERROR_STATE_MISMATCH => "Invalid or malformed JSON.",
        JSON_ERROR_CTRL_CHAR => "Control character error, possibly incorrectly encoded.",
        JSON_ERROR_SYNTAX => "Syntax error.",
        JSON_ERROR_UTF8 => "Malformed UTF-8 characters, possibly incorrectly encoded.",
        JSON_ERROR_RECURSION => "One or more recursive references in the value to be encoded.",
        JSON_ERROR_INF_OR_NAN => "One or more NAN or INF values in the value to be encoded.",
        JSON_ERROR_UNSUPPORTED_TYPE => "A value of a type that cannot be encoded was given."
    ];

    /**
     * Codifica el parámetro a JSON.
     * @param mixed $value
     * @return string
     */
    public function encode($value)
    {
        $json = json_encode($value);

        if ($code = json_last_error()) $this->throwError($code);

        return $json;
    }

    /**
     * Codifica el parámetro a JSON.
     * @param string $json
     * @param boolean $toObject
     * @return array
     */
    public function decode($json, $toObject = FALSE)
    {
        $value = json_decode($json, !$toObject);

        if ($code = json_last_error()) $this->throwError($code);

        return $value;
    }

    /**
     * Añadiéndole expresiones regulares a las aserciones de JSON.
     * @see http://json-schema.org/
     * @throws Exception
     */
    public function matchSchema($json, $schema)
    {
        $validator = new Validator();

        if (is_file($schema) && is_readable($schema)) {
            $schema = file_get_contents($schema);
        }

        $validator->check(
            $this->decode($json, TRUE), $this->decode($schema, TRUE)
        );

        return $validator->isValid();
    }

    /**
     * @throws \ErrorException
     */
    private function throwError($code)
    {
        $message = isset(self::$errorMessages[$code])
            ? self::$errorMessages[$code]
            : "Unknown error.";

        throw new \ErrorException($message);
    }
}
