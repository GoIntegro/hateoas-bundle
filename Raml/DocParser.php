<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\Raml;

// RAML.
use Raml\Parser;
// YAML.
use Symfony\Component\Yaml\Yaml;

class DocParser extends Parser
{
    /**
     * @param string $filePath
     * @return RamlDoc
     */
    public function parse($filePath)
    {
        $ramlDoc = new RamlDoc(parent::parse($filePath));

        $raml = Yaml::parse($filepath);

        if (isset($raml->schemas)) {
            // @todo Finish.
        }

        return $ramlDoc;
    }
}
