<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\DependencyInjection\Factory;

// JSON.
use GoIntegro\Hateoas\Util\JsonCoder;
// RAML.
use GoIntegro\Hateoas\Raml;

class RamlNavigatorFactory
{
    const ERROR_PARAM_TYPE = "Cannot find RAML with the given clue; a resource type or entity was expected.";

    /**
     * @var Raml\RamlDoc
     */
    private $ramlDoc;

    /**
     * @param Raml\DocParser $parser
     * @param $ramlDocPath
     */
    public function __construct(Raml\DocParser $parser, $ramlDocPath)
    {
        if (!is_readable($ramlDocPath)) {
            throw new \RuntimeException(self::ERROR_PARAM_TYPE);
        }

        // @todo Esta verificación debería estar en el DI.
        $this->ramlDoc = $parser->parse($ramlDocPath);
    }

    /**
     * @param Raml\RamlDoc $ramlDoc
     * @param JsonCoder $jsonCoder
     * @return Raml\DocNavigator
     */
    public function createNavigator(JsonCoder $jsonCoder)
    {
        return new RAML\DocNavigator($this->ramlDoc, $jsonCoder);
    }
}
