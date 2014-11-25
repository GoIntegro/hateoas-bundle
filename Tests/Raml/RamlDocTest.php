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
        // Quite justifiable.
        $rawRaml = Yaml::parse(__DIR__ . self::DEFAULT_SCHEMA_RAML);
        $ramlDoc = new RamlDoc($rawRaml, self::DEFAULT_SCHEMA_RAML);
        /* When... (Action) */
        $nay = $ramlDoc->isDefined(RamlSpec::HTTP_PUT, '/some-resources');
        $yeah = $ramlDoc->isDefined(RamlSpec::HTTP_PUT, '/some-resources/1');
        $also = $ramlDoc->isDefined(
            RamlSpec::HTTP_GET, '/some-resources/1/linked/some-relationship'
        );
        /* Then... (Assertions) */
        $this->assertFalse($nay);
        $this->assertTrue($yeah);
        $this->assertTrue($also);
    }

    public function testGettingAllowedMethodsForPath()
    {
        /* Given... (Fixture) */
        // Quite justifiable.
        $rawRaml = Yaml::parse(__DIR__ . self::DEFAULT_SCHEMA_RAML);
        $ramlDoc = new RamlDoc($rawRaml, self::DEFAULT_SCHEMA_RAML);
        /* When... (Action) */
        $allowedFiltered = $ramlDoc->getAllowedMethods('/some-resources');
        $allowedById = $ramlDoc->getAllowedMethods('/some-resources/1');
        /* Then... (Assertions) */
        $this->assertEquals(
            [RamlSpec::HTTP_GET], $allowedFiltered
        );
        $this->assertEquals(
            [RamlSpec::HTTP_GET, RamlSpec::HTTP_PUT], $allowedById
        );
    }
}
