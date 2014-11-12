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
// JSON.
use GoIntegro\Bundle\HateoasBundle\Util\JsonCoder;

class DocParser extends Parser
{
    const ERROR_ROOT_SCHEMA_VALUE = "The root section \"schemas\" have an unsupported item.",
        ERROR_UNEXPECTED_VALUE = "An unexpected value was found when parsing the RAML.";

    /**
     * @var JsonCoder
     */
    private $jsonCoder;
    /**
     * @var string
     */
    private $fileDir;

    /**
     * @param JsonCoder $jsonCoder
     */
    public function __construct(JsonCoder $jsonCoder)
    {
        $this->jsonCoder = $jsonCoder;
    }

    /**
     * @param string $filePath
     * @return RamlDoc
     */
    public function parse($filePath)
    {
        $ramlDoc = new RamlDoc(parent::parse($filePath));

        $ramlSource = Yaml::parse($filePath);
        $this->fileDir = dirname($filePath);

        if (isset($ramlSource['schemas'])) {
            foreach ($ramlSource['schemas'] as $map) {
                if (is_array($map)) {
                    $this->dereferenceIncludes($map);
                    $ramlDoc->addSchemaMap($map);
                } elseif (is_string($map)) {
                    // @todo Finish.
                } else {
                    throw new \ErrorException(self::ERROR_ROOT_SCHEMA_VALUE);
                }
            }
        }

        return $ramlDoc;
    }

    /**
     * @param array &$map
     * @return array
     */
    protected function dereferenceIncludes(array &$map)
    {
        foreach ($map as $key => &$value) {
            if (is_string($value)) {
                if (self::isInclude($value)) {
                    $value = $this->dereferenceInclude($value);
                }
            } else {
                throw new \ErrorException(self::ERROR_UNEXPECTED_VALUE);
            }
        }
    }

    /**
     * @param string $value
     * @return value
     */
    protected static function isInclude($value)
    {
        return 0 === strpos($value, '!include ');
    }

    /**
     * @param string $value
     * @return value
     * @todo Support other file types.
     */
    protected function dereferenceInclude($value)
    {
        $filePath = $this->fileDir
            . preg_replace('/^!include +/', '/', $value);

        return $this->jsonCoder->decode($filePath, TRUE);
    }
}
