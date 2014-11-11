<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\Raml;

// YAML.
use Symfony\Component\Yaml\Yaml;
// HATEOAS.
use GoIntegro\Bundle\HateoasBundle\JsonApi\ResourceEntityInterface;

class DocFinder
{
    const ERROR_PARAM_TYPE = "A resource type or entity was expected.",
        ERROR_UNKNOWN_TYPE = "The resource type is unknown.";

    /**
     * @var array
     */
    private $magicServices;

    /**
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        // @todo Esta verificación debería estar en el DI.
        $this->magicServices = isset($config['magic_services'])
            ? $config['magic_services']
            : [];
    }

    /**
     * @param ResourceEntityInterface|string $clue A resource type or entity.
     * @return array
     * @throws \InvalidArgumentException
     */
    public function find($clue)
    {
        $filepath = NULL;

        if (is_string($clue)) {
            $filepath = $this->getRamlDocFromType($clue);
        } elseif ($clue instanceof ResourceEntityInterface) {
            $filepath = $this->getRamlDocFromEntity($clue);
        } else {
            throw new \InvalidArgumentException(self::ERROR_PARAM_TYPE);
        }

        return (object) Yaml::parse($filepath);
    }

    /**
     * @param string $type
     * @return string
     * @throws \RuntimeException
     */
    private function getRamlDocFromType($type)
    {
        foreach ($this->magicServices as $service) {
            if (
                $type === $service['resource_type']
                && isset($service['raml_doc'])
                && is_readable($service['raml_doc'])
            ) {
                return $service['raml_doc'];
            }
        }

        throw new \RuntimeException(self::ERROR_UNKNOWN_TYPE);
    }
}
