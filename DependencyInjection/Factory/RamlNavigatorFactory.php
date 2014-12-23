<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\DependencyInjection\Factory;

// Symfony.
use Symfony\Component\HttpKernel\KernelInterface;
// JSON.
use GoIntegro\Json\JsonCoder;
// RAML.
use GoIntegro\Raml;

class RamlNavigatorFactory
{
    const RAML_DOC_PATH = '/config/api.raml';

    const ERROR_PARAM_TYPE = "The \"api.raml\" file was not found in the config dir - possibly \"app/config/\".";

    /**
     * @var KernelInterface
     */
    private $kernel;
    /**
     * @var Raml\DocParser
     */
    private $parser;

    /**
     * @param KernelInterface $kernel
     * @param Raml\DocParser $parser
     */
    public function __construct(
        KernelInterface $kernel, Raml\DocParser $parser
    )
    {
        $this->kernel = $kernel;
        $this->parser = $parser;
    }

    /**
     * @param JsonCoder $jsonCoder
     * @return Raml\DocNavigator
     */
    public function createNavigator(JsonCoder $jsonCoder)
    {
        $ramlDocPath = $this->kernel->getRootDir() . self::RAML_DOC_PATH;

        if (!is_readable($ramlDocPath)) {
            throw new \RuntimeException(self::ERROR_PARAM_TYPE);
        }

        $ramlDoc = $this->parser->parse($ramlDocPath);

        return new Raml\DocNavigator($ramlDoc, $jsonCoder);
    }
}
