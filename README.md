[GOintegro](http://www.gointegro.com/en/) / HATEOAS
===================================================
This is a library and Symfony 2 bundle that allows you to magically expose your Doctrine 2 mapped entities as resources in a [HATEOAS](http://www.ics.uci.edu/~fielding/pubs/dissertation/rest_arch_style.htm) API and supports the full spec of [JSON-API](http://jsonapi.org/) for serializing and fetching; sparse fields, includes, filtering, sorting, the works.

Pagination and [faceted searches](http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/search-facets.html) are supported as an extension to the JSON-API spec. (Although the extensions are not yet accompanied by the corresponding [profiles](http://jsonapi.org/extending/).)

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
```
=======
