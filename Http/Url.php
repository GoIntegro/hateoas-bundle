<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\Http;

// Utils.
use GoIntegro\Bundle\HateoasBundle\Util\Inflector;

/**
 * Una URL HTTP.
 */
class Url
{
    const DEFAULT_SCHEME = 'http';
    const INVALID_URL_ERROR = "La URL dada es inválida y no pudo ser analizada.";

    private $scheme; // El protocolo.
    private $host;
    private $port;
    private $user;
    private $pass;
    private $path;
    private $query; // Después del símbolo '?'
    private $fragment; // Después del símbolo '#'
    private $root; // La base de una URL relativa.
    private $original; // La URL a partir de la cuál fue creada la instancia.

    /**
     * Construye una instancia a partir de un objeto con una interfaz similar.
     * @param string $url
     * @param Url $root NULL por defecto.
     */
    public static function fromString($url, Url $root = null)
    {
        $instance = new static();
        $components = static::parseUrl($url);
        foreach ($components as $component => $value) {
            $method = 'set' . Inflector::camelize($component);
            call_user_func(array($instance, $method), $value);
        }
        $instance->setOriginal($url);
        $instance->setRoot($root);

        return $instance;
    }

    /**
     * Maneja las llamadas a métodos no definidos.
     * @param string $method
     * @param array $args
     * @return mixed La respuesta del método existente.
     * @throws \BadMethodCallException Si el método llamado no existe.
     */
    public function __call($method, $args)
    {
        $result = null;
        $property = lcfirst(substr($method, 3));

        if (property_exists($this, $property)) {
            $action = substr($method, 0, 3);
            switch ($action) {
                case 'set':
                    $this->$property = array_shift($args);
                    $result = $this;
                    break;
                case 'get':
                    $result = $this->$property;
                    break;
                default:
                    throw new \BadMethodCallException();
            }
        } else {
            throw new \BadMethodCallException();
        }

        return $result;
    }

    /**
     * Convierte la instancia en un array.
     */
    public function toArray()
    {
        return array(
            'scheme' => $this->scheme,
            'host' => $this->host,
            'port' => $this->port,
            'user' => $this->user,
            'pass' => $this->pass,
            'path' => $this->path,
            'query' => $this->query,
            'fragment' => $this->fragment
        );
    }

    /**
     * Convierte la instancia en un string.
     */
    public function __toString()
    {
        if ($root = $this->getRoot()) {
            $this->toAbsoluteUrl($root); // Deliberadamente redundante.
        }

        return $this->isUrlAbsolute()
            ? $this->getOriginal()
            : http_build_url($this->toArray());
    }

    /**
     * Obtiene la URL absoluta de un path.
     * @param Url $root
     * @param Url $url
     * @return string La URL absoluta.
     */
    public function toAbsoluteUrl(Url $root = null)
    {
        $root = $this->getRoot() ?: $root;
        if (!$root->isUrlAbsolute()) {
            throw new \InvalidArgumentException();
        }
        if (!$this->isUrlAbsolute()) {
            $this->setScheme($root->getScheme());
            $this->setHost($root->getHost());
            if (!$this->isPathAbsolute()) {
                $path = $root->getPath();
                $path = substr($path, 0, strrpos($path, '/') + 1);
                $this->setPath($path . $this->getPath());
            }
        }
        return $this;
    }

    /**
     * Verifica si el path es absoluto.
     */
    public function isPathAbsolute()
    {
        return 0 === strpos($this->getPath(), '/');
    }

    /**
     * Verifica si la URL es absoluta.
     */
    public function isUrlAbsolute()
    {
        return $this->getScheme() && $this->getHost();
    }

    /**
     * Envoltorio de la función parse_url() que arregla un bug conocido en PHP5.
     * Lamentablemente las versiones de PHP5 previas a 5.4.7 no reconocen el schema
     * cuando está escrito en su versión minificada, "//domain.ext/some/path".
     * @see http://php.net/manual/fr/function.parse-url.php
     * @todo Deprecar una vez que actualicemos la versión de PHP5 en prod.
     */
    public static function parseUrl($url)
    {
        $components = parse_url($url);

        if (FALSE === $components) {
            throw new \InvalidArgumentException(static::INVALID_URL_ERROR);
        }

        static $pattern = '/^\\/\\/[^.]+\\.[^\\/]+/';

        if (
            isset($components['path'])
            && preg_match($pattern, $components['path'], $matches)
        ) {
            $components['scheme'] = static::DEFAULT_SCHEME;
            $components['host'] = $matches[0];
            $components['path'] = str_replace($matches[0], '', $components['path']);
        } elseif (isset($components['host']) && empty($components['scheme'])) {
            $components['scheme'] = static::DEFAULT_SCHEME;
        }

        return $components;
    }
}
