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
class PostsTest extends ApiTestCase
{
    const RESOURCE_PATH = '/api/v1/posts';

    /**
     * Obtiene los fixtures de este test case.
     * @return array <FixtureInterface>
     */
    protected static function getFixtures()
    {
        return [new SocialDataFixture];
    }

    public function testPosting201()
    {
        /* Given... (Fixture) */
        $url = $this->getRootUrl() . self::RESOURCE_PATH;
        $body = ['posts' => ['content' => "This is quite a post."]];
        $client = $this->buildHttpClient($url, 'this_guy', 'cl34rt3xt')
            ->setMethod('POST')
            ->setBody($body);
        /* When... (Action) */
        $transfer = $client->exec();
        /* Then... (Assertions) */
        $message = $transfer . "\n";
        $this->assertResponseCreated($client, $message);
        $this->assertJsonApiSchema($transfer, $message);

        return json_decode($transfer);
    }

    public function testGettingMany200()
    {
        /* Given... (Fixture) */
        $url = $this->getRootUrl() . self::RESOURCE_PATH;
        $client = $this->buildHttpClient($url, 'this_guy', 'cl34rt3xt');
        /* When... (Action) */
        $transfer = $client->exec();
        /* Then... (Assertions) */
        $message = $transfer . "\n";
        $this->assertResponseOK($client, $message);
        $this->assertJsonApiSchema($transfer, $message);
    }

    /**
     * @depends testPosting201
     */
    public function testGettingOne200()
    {
        /* Given... (Fixture) */
        $url = $this->getRootUrl() . self::RESOURCE_PATH;
        $client = $this->buildHttpClient($url, 'this_guy', 'cl34rt3xt');
        /* When... (Action) */
        $transfer = $client->exec();
        /* Then... (Assertions) */
        $message = $transfer . "\n";
        $this->assertResponseOK($client, $message);
        $this->assertJsonApiSchema($transfer, $message);
    }

    /**
     * @depends testPosting201
     */
    public function testPutting200(\stdClass $post)
    {
        /* Given... (Fixture) */
        $url = $this->getRootUrl() . self::RESOURCE_PATH
            . '/' . $post->posts->id;
        $body = ['posts' => ['content' => "No it's not."]];
        $client = $this->buildHttpClient($url, 'this_guy', 'cl34rt3xt')
            ->setMethod('PUT')
            ->setBody($body);
        /* When... (Action) */
        $transfer = $client->exec();
        /* Then... (Assertions) */
        $message = $transfer . "\n";
        $this->assertResponseOK($client, $message);
        $this->assertJsonApiSchema($transfer, $message);
    }
}
