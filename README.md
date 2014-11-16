[GOintegro](http://www.gointegro.com/en/) / HATEOAS
===================================================
This is a library and Symfony 2 bundle that allows you to magically expose your Doctrine 2 mapped entities as resources in a [HATEOAS](http://www.ics.uci.edu/~fielding/pubs/dissertation/rest_arch_style.htm) API and supports the full spec of [JSON-API](http://jsonapi.org/) for serializing and fetching; sparse fields, includes, filtering, sorting, the works.

Pagination and [faceted searches](http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/search-facets.html) are supported as an extension to the JSON-API spec. (Although the extensions are not yet accompanied by the corresponding [profiles](http://jsonapi.org/extending/).)

Support for creating, updating, and deleting resources according to JSON-API is in progress. Currently, creating, updating, and deleting one or many resources magically is supported - but the service that handles these operations cannot be overridden. Default validation is handled by [Symfony's Validator Component](http://symfony.com/doc/current/book/validation.html), so you can configure basic validation right on your entities.

Try it out
==========

Check out [the example app project](https://github.com/skqr/hateoas-bundle-example), so you can feel the magic in your finger tips without much ado.

Installation
============

Step 1: Download the Bundle
---------------------------

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```bash
$ composer require gointegro/hateoas-bundle
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Step 2: Enable the Bundle
-------------------------

Then, enable the bundle by adding the following line in the `app/AppKernel.php`
file of your project:

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...

            new GoIntegro\Bundle\HateoasBundle\GoIntegroHateoasBundle(),
        );

        // ...
    }

    // ...
}
?>
```

Step 3: Add these parameters
----------------------------

```yaml
# app/config/parameters.yml

# HATEOAS API
api.base_url: "http://api.gointegro.com"
api.url_path: "/api/v2"
api.resource_class_path: "Rest2/Resource"
```

Step 4: Add these routes
------------------------

```yaml
# app/config/routing.yml

# Place it underneath it all - it contains a catch-all route.
go_integro_hateoas:
    resource: "@GoIntegroHateoasBundle/Resources/config/routing.yml"
    prefix: /api/v2
```

Usage
=====

Have your entity implement the resource interface.

```php
<?php
use GoIntegro\Bundle\HateoasBundle\JsonApi\ResourceEntityInterface

class User implements ResourceEntityInterface {}
?>
```

You can now create a HATEOAS resource for it and serialize it as JSON-API. And, if your controller extends the HATEOAS controller, you can even return a neat HTTP response with the JSON-API Content-Type.

```php
<?php
use GoIntegro\Bundle\HateoasBundle\Controller\Controller,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class UsersController extends Controller {
    /**
     * @Route("/users/{user}", name="api_get_user", methods="GET")
     * @return \GoIntegro\Bundle\HateoasBundle\Http\JsonResponse
     */
    public function getUserAction(User $user) {
      $resourceManager = $serviceContainer->get('hateoas.resource_manager');
      $resource = $resourceManager->createResourceFactory()
        ->setEntity($user)
        ->create();

      $json = $resourceManager->createSerializerFactory()
        ->setDocumentResources($resource)
        ->create()
        ->serialize();

      return $this->createETagResponse($json);
    }
?>
```

Seems like it could get awfully repetitive, doesn't it?

That's why you don't have to.

Just register your entity as a "magic service".

```yaml
# app/config/config.yml

# The resource_type *must* match the calculated type - for now.
go_integro_hateoas:
  json_api:
    magic_services:
      - resource_type: users
        entity_class: GoIntegro\Bundle\ExampleBundle\Entity\User
      - resource_type: posts
        entity_class: GoIntegro\Bundle\ExampleBundle\Entity\Post
      - resource_type: comments
        entity_class: GoIntegro\Bundle\ExampleBundle\Entity\Comment
```

And you get the following for free.

```
/users
/users/1
/users/1,2,3
/users/1/name
/users/1/linked/posts
/posts/1/linked/author
/posts?author=1
/posts?author=1,2,3
/users?sort=name,-birth-date
/users?include=posts,posts.comments
/users?fields=name,email
/users?include=posts,posts.comments&fields[users]=name&fields[posts]=content
/users?page=1
/users?page=1&size=10
```

And more. And any combination. Sweet.

But you need to have some control over what you expose, right? Got you covered.

You can optionally define a class like this for your entity, and optionally define any of the properties and methods you will see within.

```php
<?php
namespace GoIntegro\Bundle\ExampleBundle\Rest2\Resource;

// Symfony 2.
use Symfony\Component\DependencyInjection\ContainerAwareInterface,
  Symfony\Component\DependencyInjection\ContainerAwareTrait;
// HATEOAS.
use GoIntegro\Bundle\HateoasBundle\JsonApi\EntityResource;

class UserResource extends EntityResource implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @var array
     */
    public static $fieldWhitelist = ['name', 'surname', 'email'];
    /**
     * You wouldn't ever use both a blacklist and a whitelist.
     * @var array
     */
    public static $fieldBlacklist = ['password'];
    /**
     * @var array
     */
    public static $relationshipBlacklist = ['groups'];
    /**
     * These appear as top-level links but not in the resource object.
     * @var array
     */
    public static $linkOnlyRelationships = ['followers'];

    /**
     * By injecting a field we can have both the JSON-API reserved key "type" and our own "user-type" attribute in the resource object.
     * @return string
     * @see http://jsonapi.org/format/#document-structure-resource-object-attributes
     */
    public function injectUserType()
    {
        return $this->entity->getType();
    }

    /**
     * We can use services if we implement the ContainerAwareInterface.
     * @return string
     */
    public function injectSomethingExtraordinary()
    {
        return $this->container->get('mystery_machine')->amaze();
    }
}
?>
```

Check out the unit tests for more details.

Security
--------

Access control is handled by [Symfony's Security Component](http://symfony.com/doc/current/components/security/introduction.html), so either [security voters](http://symfony.com/doc/current/cookbook/security/voters_data_permission.html) or [ACL](http://symfony.com/doc/current/cookbook/security/acl.html) must be configured.

If you don't want security at all, just configure a single voter accepting anything that implements `GoIntegro\Bundle\HateoasBundle\JsonApi\ResourceEntityInterface`. Not the best advice ever, though.

There is an unresolved [issue related to access control and pagination](https://github.com/GoIntegro/hateoas-bundle/issues/10).

Ghosts
------

I know what you're thinking - what if my resource does not have an entity? Am I left to fend for myself in the cold dark night?

Not a chance. Ghosts are there with you, in the dark. To help you out.

"Ghosts" are what you create ghost-resources from instead of persisted entities. They are created within a custom HATEOAS controller, and can be fed to the resource factory in lieu of an entity. They define their relationships rather than the ORM knowing about them beforehand.

What you use for an Id, and the extent to which you use them is entirely up to you.

```php
<?php

namespace GoIntegro\Bundle\SomeBundle\Rest2\Ghost;

// Entidades.
use GoIntegro\Bundle\SomeBundle\Entity\Star;
// JSON-API.
use GoIntegro\Bundle\HateoasBundle\JsonApi\GhostResourceEntity,
    GoIntegro\Bundle\HateoasBundle\Metadata\Resource\ResourceRelationship,
    GoIntegro\Bundle\HateoasBundle\Metadata\Resource\ResourceRelationships;
// Colecciones.
use Doctrine\Common\Collections\ArrayCollection;

class StarCluster implements GhostResourceEntity
{
    /**
     * @var string
     */
    private $id;
    /**
     * @var Star
     */
    private $brightestStar;
    /**
     * @var ArrayCollection
     */
    private $stars;

    /**
     * @param string $id
     */
    public function __construct($id)
    {
        $this->id = $id;
        $this->stars = new ArrayCollection;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param Star $star
     * @return self
     */
    public function setBrightestStar(Star $star)
    {
        $this->brightestStar = $star;

        return $this;
    }

    /**
     * @return Star
     */
    public function getBrightestStar()
    {
        return $this->brightestStar;
    }

    /**
     * @return ArrayCollection
     */
    public function getStars()
    {
        return $this->stars;
    }

    /**
     * @param \GoIntegro\Bundle\SomeBundle\Entity\Star $star
     * @return self
     */
    public function addStar(Star $star)
    {
        $this->stars[] = $star;

        return $this;
    }

    /**
     * @return ResourceRelationships
     */
    public static function getRelationships()
    {
        $relationships = new ResourceRelationships;
        $relationships->toMany['stars'] = new ResourceRelationship(
            'GoIntegro\Bundle\SomeBundle\Entity\Star',
            'stars', // resource type
            'stars', // resource sub-type
            'toMany', // relationship kind
            'stars', // relationship name
            'stars' // mapping field
        );
        $relationships->toOne['brightest-star'] = new ResourceRelationship(
            'GoIntegro\Bundle\SomeBundle\Entity\Star',
            'stars',
            'stars',
            'toOne',
            'brightest-star',
            'brightestStar'
        );

        return $relationships;
    }
}
?>
```

Testing
-------

The bundle comes with a comfy PHPUnit test case designed to make HATEOAS API functional tests.

A simple HTTP client makes the request and assertions are made using [JSON schemas](http://json-schema.org/).

```php
<?php
namespace GoIntegro\Entity\Suite;

// Testing.
use GoIntegro\Test\PHPUnit\ApiTestCase;
// Fixtures.
use GoIntegro\DataFixtures\ORM\Standard\SomeDataFixture;

class SomeResourceTest extends ApiTestCase
{
    const RESOURCE_PATH = '/api/v2/some-resource',
        RESOURCE_JSON_SCHEMA = '/schemas/some-resource.json';

    /**
     * Doctrine 2 data fixtures to load *before the test case*.
     * @return array <FixtureInterface>
     */
    protected static function getFixtures()
    {
        return array(new SomeDataFixture);
    }

    public function testGettingMany200()
    {
        /* Given... (Fixture) */
        $url = $this->getRootUrl() . self::RESOURCE_PATH;
        $client = $this->createHttpClient($url);
        /* When... (Action) */
        $transfer = $client->exec();
        /* Then... (Assertions) */
        $this->assertResponseOK($client);
        $this->assertJsonApiSchema($transfer);
        $schema = __DIR__ . self::RESOURCE_JSON_SCHEMA;
        $this->assertJsonSchema($schema, $transfer);
    }

    public function testGettingSortedBySomeCustomField400()
    {
        /* Given... (Fixture) */
        $url = $this->getRootUrl()
            . self::RESOURCE_PATH
            . '?sort=some-custom-field';
        $client = $this->createHttpClient($url);
        /* When... (Action) */
        $transfer = $client->exec();
        /* Then... (Assertions) */
        $this->assertResponseBadRequest($client);
    }
}
?>
```

Error handling
--------------

JSON-API covers [how to inform about errors](http://jsonapi.org/format/#errors) as well.

Our implementation isn't trully as complete as could be, but you can tell Twig to use our ExceptionController instead of its own in order to have your errors serialized properly.

```yaml
# app/config/config.yml

twig:
  exception_controller: 'GoIntegro\Bundle\HateoasBundle\Controller\ExceptionController::showAction'
```

Fetching multiple URLs
----------------------

Here's something useful but not RESTful.

You can use the */multi* action to fetch several JSON-API URLs, and this won't even result in an additional HTTP request.

```
/multi?url[]=%2Fapi%2Fv1%2Fusers&url[]=%2Fapi%2Fv1%2Fposts
```

The URLs just need to be encoded, but you can use the full set of JSON-API functionality supported.

A *blender* service wil make sure to notify you if, by chance, the URLs provided are not mergeable.

Feedback
========

Feel free to **open an issue** if you have valuable (or otherwise) feedback. Hoping to hear from you (either way).
