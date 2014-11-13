<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace JsonApi;

// Tests.
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
// Mocks.
use Codeception\Util\Stub;
// RAML.
use GoIntegro\Bundle\HateoasBundle\Raml\DocParser,
    GoIntegro\Bundle\HateoasBundle\Raml\RamlDoc;

class DocParserTest extends TestCase
{
    const RAML_PATH = '/../../example/src/HateoasInc/Bundle/ExampleBundle/Resources/raml/posts.raml',
        TEST_SCHEMA = "This is the schema";

    public function testParsingRamlDoc()
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
        /* When... (Action) */
        $ramlDoc = $parser->parse(__DIR__ . self::RAML_PATH);
        /* Then... (Assertions) */
        $this->assertInstanceOf(
            'GoIntegro\Bundle\HateoasBundle\Raml\RamlDoc', $ramlDoc
        );
        $this->assertEquals(
            self::TEST_SCHEMA,
            $parser->createNavigator($ramlDoc)
                ->findRequestSchema(RamlDoc::HTTP_POST, '/posts')
        );
    }
}
