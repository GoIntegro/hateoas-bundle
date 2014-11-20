<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\Raml;

// Tests.
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
// Mocks.
use Codeception\Util\Stub;

class DocFinderTest extends TestCase
{
    const RAML_PATH = '/../Resources/raml/some-resources.raml',
        TEST_SCHEMA = "This is the schema";

    public function testCreatingDocNavigator()
    {
        /* Given... (Fixture) */
        $jsonCoder = Stub::makeEmpty(
            'GoIntegro\Bundle\HateoasBundle\Util\JsonCoder',
            ['decode' => function($filePath) {
                if (!is_readable($filePath)) {
                    throw new \ErrorException("The file is not readable.");
                }

                return self::TEST_SCHEMA;
            }]
        );
        $parser = new DocParser($jsonCoder);
        $ramlDoc = $parser->parse(__DIR__ . self::RAML_PATH);
        $finder = new DocFinder($parser, $jsonCoder);
        /* When... (Action) */
        $navigator = $finder->createNavigator($ramlDoc);
        /* Then... (Assertions) */
        $this->assertInstanceOf(
            'GoIntegro\Bundle\HateoasBundle\Raml\DocNavigator', $navigator
        );
    }
}
