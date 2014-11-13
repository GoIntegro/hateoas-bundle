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
    GoIntegro\Bundle\HateoasBundle\Raml\DocNavigator,
    GoIntegro\Bundle\HateoasBundle\Raml\RamlDoc;

/**
 * @todo Depends on the DocParserTest.
 */
class DocNavigatorTest extends TestCase
{
    const DEFAULT_SCHEMA_RAML = '/../Resources/raml/default-schema.raml',
        INLINE_BODY_SCHEMA_RAML = '/../Resources/raml/inline-body-schema.raml',
        TEST_SCHEMA = "This is the schema",
        INLINE_BODY_SCHEMA = <<<'SCHEMA'
{
  "$schema": "http://json-schema.org/schema",
  "type": "object",
  "description": "A random resource.",
  "properties": {
    "some-resources": {
      "type": "object",
      "properties": {
          "id":  { "type": "string" },
          "content": { "type": "string" },
          "links": {
              "type": "object",
              "properties": {
                  "author": { "type": "string" },
                  "comments": { "type": "array" }
              },
              "required": [ "author" ]
          }
      },
      "required": [ "content" ]
  }
}
SCHEMA;

    public function testFindingDefaultSchema()
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
        $ramlDoc = $parser->parse(__DIR__ . self::DEFAULT_SCHEMA_RAML);
        $navigator = new DocNavigator($ramlDoc, $jsonCoder);
        /* When... (Action) */
        $schema = $navigator->findRequestSchema(
            RamlDoc::HTTP_POST, '/some-resources'
        );
        /* Then... (Assertions) */
        $this->assertEquals(self::TEST_SCHEMA, $schema);
    }

    public function testFindingInlineBodySchema()
    {
        /* Given... (Fixture) */
        $jsonCoder = Stub::makeEmpty(
            'GoIntegro\Bundle\HateoasBundle\Util\JsonCoder',
            [
                'decode' => function($filePath) {
                    if (!is_readable($filePath)) {
                        throw new \ErrorException("The file is not readable.");
                    }

                    return self::TEST_SCHEMA;
                },
                'assertJsonSchema' => function($json) { return TRUE; }
            ]
        );
        $parser = new DocParser($jsonCoder);
        $ramlDoc = $parser->parse(__DIR__ . self::INLINE_BODY_SCHEMA_RAML);
        $navigator = new DocNavigator($ramlDoc, $jsonCoder);
        /* When... (Action) */
        $schema = $navigator->findRequestSchema(
            RamlDoc::HTTP_POST, '/some-resources'
        );
        /* Then... (Assertions) */
        $this->assertEquals(self::INLINE_BODY_SCHEMA, $schema);
    }
}
