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
// Yaml.
use Symfony\Component\Yaml\Yaml;

class RamlDocTest extends TestCase
{
    const DEFAULT_SCHEMA_RAML = '/../Resources/raml/default-schema.raml';

    public function testCheckingWhetherRequestIsDefined()
    {
        /* Given... (Fixture) */
        $apiDef = Stub::makeEmpty('Raml\ApiDefinition');
        // Quite justifiable.
        $rawRaml = Yaml::parse(__DIR__ . self::DEFAULT_SCHEMA_RAML);
        $ramlDoc = new RamlDoc($apiDef, $rawRaml, self::DEFAULT_SCHEMA_RAML);
        /* When... (Action) */
        $nay = $ramlDoc->isDefined(RamlDoc::HTTP_PUT, '/some-resources');
        $yeah = $ramlDoc->isDefined(RamlDoc::HTTP_PUT, '/some-resources/1');
        $also = $ramlDoc->isDefined(
            RamlDoc::HTTP_GET, '/some-resources/1/linked/some-relationship'
        );
        /* Then... (Assertions) */
        $this->assertFalse($nay);
        $this->assertTrue($yeah);
        $this->assertTrue($also);
    }
}
