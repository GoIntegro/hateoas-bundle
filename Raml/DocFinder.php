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
// JSON.
use GoIntegro\Bundle\HateoasBundle\Util\JsonCoder;

class DocFinder
{
    const ERROR_PARAM_TYPE = "Cannot find RAML with the given clue; a resource type or entity was expected.",
        ERROR_UNKNOWN_TYPE = "The resource type is unknown or has no RAML configured.";

    /**
     * @var DocParser
     */
    private $parser;
    /**
     * @var JsonCoder
     */
    private $jsonCoder;
    /**
     * @var array
     */
    private $magicServices;

    /**
     * @param DocParser $parser
     * @param JsonCoder $jsonCoder
     * @param array $config
     */
    public function __construct(
        DocParser $parser, JsonCoder $jsonCoder, array $config = []
    )
    {
        $this->parser = $parser;
        $this->jsonCoder = $jsonCoder;
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
        $filePath = NULL;

        if (is_string($clue)) {
            $filePath = $this->getRamlDocFromType($clue);
        } elseif ($clue instanceof ResourceEntityInterface) {
            $filePath = $this->getRamlDocFromEntity($clue);
        } else {
            throw new \InvalidArgumentException(self::ERROR_PARAM_TYPE);
        }

        return $this->parser->parse($filePath);
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

    /**
     * @param ResourceEntityInterface $entity
     * @return string
     * @throws \RuntimeException
     */
    private function getRamlDocFromEntity(ResourceEntityInterface $entity)
    {
        $className = get_class($entity);

        foreach ($this->magicServices as $service) {
            if (
                (
                    $className === $service['entity_class']
                    || is_subclass_of($entity, $service['entity_class'])
                )
                && isset($service['raml_doc'])
                && is_readable($service['raml_doc'])
            ) {
                return $service['raml_doc'];
            }
        }

        throw new \RuntimeException(self::ERROR_UNKNOWN_TYPE);
    }

    /**
     * @param RamlDoc $ramlDoc
     * @return DocNavigator
     */
    public function createNavigator(RamlDoc $ramlDoc)
    {
        return new DocNavigator($ramlDoc, $this->jsonCoder);
    }
}
