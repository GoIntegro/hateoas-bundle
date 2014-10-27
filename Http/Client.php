<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */

namespace GoIntegro\Bundle\HateoasBundle\Http;

/**
 * Cliente de REST para
 * @see http://php.net/manual/fr/book.curl.php
 */
class Client
{
    const BYTE_UNIT = 'K',
        KILOBYTE_UNIT = 'KB',
        BYTE_LIMIT = 5120, // 2KB
        KILOBYTE = 1024,
        MEGABYTE = 1048576,
        WARNING_SIZE = "\nLa respuesta de %s %s pesa %s.\n";

    /**
     * @var array Las opciones definidas por defecto.
     */
    private static $defaults = array(
        CURLINFO_HEADER_OUT     => true,
        CURLOPT_RETURNTRANSFER  => true
    );

    /**
     * @var resource El handler del recurso cURL.
     */
    private $curlHandler;

    /**
     * @var array Los headers definidos hasta el momento.
     */
    public $head = array();

    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $method;

    /**
     * Construye la instancia.
     * @param string $url Una URL. Opcional.
     */
    public function __construct($url = null)
    {
        $this->url = $url;
        $this->curlHandler = curl_init();
        curl_setopt_array($this->curlHandler, static::$defaults);
        if ($url) {
            curl_setopt($this->curlHandler, CURLOPT_URL, $url);
        }
    }

    /**
     * Define las opciones de cURL.
     * @param array Un diccionario de opciones y valores (válidos).
     * @return Client Esta instancia.
     * @see http://php.net/manual/es/function.curl-setopt.php
     */
    public function setOptions(array $settings)
    {
        curl_setopt_array($this->curlHandler, $settings);
        return $this;
    }

    /**
     * Define una opción de cURL.
     * @param mixed $option La constante correspondiente a la opción.
     * @param mixed $value El valor de la opción.
     * @return Client Esta instancia.
     * @see http://php.net/manual/es/function.curl-setopt.php
     */
    public function setOption($option, $value)
    {
        curl_setopt($this->curlHandler, $option, $value);
        return $this;
        }

    /**
     * Define la cabeza de un pedido.
     * @param array El contenido de la cabeza.
     * @throws \ErrorException
     */
    public function setMethod($method)
    {
        $this->method = $method;
        curl_setopt($this->curlHandler, CURLOPT_CUSTOMREQUEST, $method);
        return $this;
    }

    /**
     * Define la cabeza de un pedido.
     * @param array El contenido de la cabeza.
     * @throws \ErrorException
     */
    public function setHead(array $head)
    {
        $this->head = $head;
        curl_setopt($this->curlHandler, CURLOPT_HTTPHEADER, $this->head);
        return $this;
    }

    /**
     * Define la cabeza de un pedido.
     * @param array El contenido de la cabeza.
     * @throws \ErrorException
     */
    public function addHeaders(array $headers)
    {
        $this->head = array_merge($this->head, $headers);
        curl_setopt($this->curlHandler, CURLOPT_HTTPHEADER, $this->head);
        return $this;
    }

    /**
     * Define el cuerpo de un pedido.
     * @param string El contenido del cuerpo.
     * @throws \ErrorException
     */
    public function setBody($body)
    {
        $body = json_encode($body);

        if ($error = json_last_error()) {
            throw new \ErrorException($error);
        }

        curl_setopt($this->curlHandler, CURLOPT_POSTFIELDS, $body);

        return $this;
    }

    public function attachFile($name, $filepath)
    {
        $data[$name] = '@'.$filepath;

        $this->setOptions(array (
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $data
        ));
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function exec()
    {
        $transfer = curl_exec($this->curlHandler);

        if ($message = curl_error($this->curlHandler)) {
            throw new \Exception($message);
        }

        // http://www.php.net/manual/en/function.strlen.php
        if (static::BYTE_LIMIT < ($length = strlen($transfer))) {
            echo sprintf(
                static::WARNING_SIZE,
                $this->method,
                $this->url,
                $this->bytesToSize($length)
            );
        }

        return $transfer;
    }

    /**
     * Obtiene información sobre el pedido.
     * @param mixed $info La constante correspondiente a la información.
     * @return mixed La información.
     */
    public function getInfo($key = null)
    {
        $info = $key
            ? curl_getinfo($this->curlHandler, $key)
            : curl_getinfo($this->curlHandler);
        return $info;
    }

    /**
     * Destruye la instancia.
     */
    public function __destruct()
    {
        curl_close($this->curlHandler);
    }

    /**
     * @param integer $bytes
     * @param integer $precision
     * @return string
     */
    private function bytesToSize($bytes, $precision = 2)
    {
        return $bytes >= static::KILOBYTE && $bytes < static::MEGABYTE
            ? round($bytes / static::KILOBYTE, $precision) . static::KILOBYTE_UNIT
            : $bytes . static::BYTE_UNIT;
    }
}
