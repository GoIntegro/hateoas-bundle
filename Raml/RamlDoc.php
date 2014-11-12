<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\Raml;

// RAML.
use Raml\ApiDefinition;

class RamlDoc
{
    /**
     * @var ApiDefinition
     */
    private $apiDef;
    /**
     * @var array
     */
    private $schemaMaps = [];

    /**
     * @param $apiDef
     */
    public function __construct(ApiDefinition $apiDef)
    {
        $this->apiDef = $apiDef;
    }

    /**
     * @param array $map
     * @return self
     */
    public function addSchemaMap(array $map)
    {
        $this->schemaMaps[] = $map;
    }

    /**
     * @param string $name
     * @return \stdClass|NULL
     */
    public function getSchema($name)
    {
        foreach ($this->schemaMaps as $map) {
            foreach ($this->map as $key => $schema) {
                if ($key === $name) return $schema;
            }
        }

        return NULL;
    }

    /**
     * @param string $name
     * @param array $args
     */
    public function __call($name, $args)
    {
        $method = [$this->apiDef, $name];

        if (is_callable($method)) {
            return call_user_func_array($method, $args);
        } else {
            throw \BadMethodCallException("No such method here.");
        }
    }
}
