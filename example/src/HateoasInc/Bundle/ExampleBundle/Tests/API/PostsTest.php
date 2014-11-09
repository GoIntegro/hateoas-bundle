<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace HateoasInc\Bundle\ExampleBundle\Tests\API;

// Testing.
use GoIntegro\Bundle\HateoasBundle\Test\PHPUnit\ApiTestCase;
// Fixtures.
use HateoasInc\Bundle\ExampleBundle\DataFixtures\ORM\SocialDataFixture;

/**
 * Tests the functionality implemented in the class.
 *
 */
class StoresTest extends ApiTestCase
{
    const RESOURCE_PATH = '/api/v2/posts';

    /**
     * Obtiene los fixtures de este test case.
     * @return array <FixtureInterface>
     */
    protected static function getFixtures()
    {
        return [new SocialDataFixture];
    }

    public function testGettingMany200()
    {
        /* Given... (Fixture) */
        $url = $this->getRootUrl() . self::RESOURCE_PATH;
        $client = $this->createHttpClient($url);
        /* When... (Action) */
        $transfer = $client->exec();
        /* Then... (Assertions) */
        $message = $transfer . "\n";
        $this->assertResponseOK($client, $message);
        $this->assertJsonApiSchema($transfer, $message);
    }
}
