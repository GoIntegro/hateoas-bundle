<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\Util;

// JSON.
use GoIntegro\Json\JsonCoder;
// Symfony 2.
use Symfony\Component\HttpKernel\KernelInterface;
// JSON-API.
use GoIntegro\Hateoas\JsonApi\JsonApiSpec;
// JSON Schema.
use GoIntegro\Hateoas\Raml\JsonSchemaSpec;

/**
 * La fachada del servicio de validación de JSON schemas.
 *
 * Not using the herrera-io/json lib because validation code should not
 * throw exceptions.
 */
class JsonValidator
{
    /**
     * @var KernelInterface
     */
    private $kernel;
    /**
     * @var JsonCoder
     */
    private $jsonCoder;

    /**
     * @param KernelInterface $kernel
     * @param JsonCoder $jsonCoder
     */
    public function __construct(KernelInterface $kernel, JsonCoder $jsonCoder)
    {
        $this->kernel = $kernel;
        $this->jsonCoder = $jsonCoder;
    }

    /**
     * @param string $json
     * @return boolean
     */
    public function assertJsonApi($json)
    {
        $schema = $this->kernel->locateResource(
            JsonApiSpec::JSON_API_SCHEMA_PATH
        );

        return $this->jsonCoder->matchSchema($json, $schema);
    }

    /**
     * @param string $json
     * @return boolean
     */
    public function assertJsonSchema($json)
    {
        $schema = $this->kernel->locateResource(
            JsonSchemaSpec::JSON_SCHEMA_SCHEMA_PATH
        );

        return $this->jsonCoder->matchSchema($json, $schema);
    }
}
