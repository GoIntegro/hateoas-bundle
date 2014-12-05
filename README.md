# [GOintegro](http://www.gointegro.com/en/) / HATEOAS (bundle)

[![Build Status](https://travis-ci.org/gointegro/hateoas.svg?branch=master)](https://travis-ci.org/gointegro/hateoas/) [![Code Climate](https://codeclimate.com/github/gointegro/hateoas/badges/gpa.svg)](https://codeclimate.com/github/gointegro/hateoas/)

This is a Symfony 2 bundle for the [GOintegro HATEOAS lib](https://github.com/gointegro/hateoas) that uses a Doctrine 2 entity map and a [RAML](http://raml.org/) API definition to conjure a [HATEOAS](http://www.ics.uci.edu/~fielding/pubs/dissertation/rest_arch_style.htm) API, following the [JSON-API](http://jsonapi.org/) specification.

You don't get scaffolds. You get a working API.

You get a working API with features sweeter than [a Bobcat's self-esteem](http://s3.amazonaws.com/theoatmeal-img/comics/bobcats_thursday/mirror.png).

## Features

Here's what I mean.

* Modeling, documenting, and creating your API are [one step](#api-definition).
* Flat, referenced JSON serialization.
  * Clear distinction between scalar fields and linked resources.
* Magic controllers.
  * Fetching resources, with support for:
    * Sparse fields;
    * Linked resources expansion;
    * [Standarized filtering](#query-filters) and sorting;
    * Pagination;
    * Resource metadata, such as facets in a search.
  * Altering resources, with support for:
    * Processing multiple actions in one request;
    * Request validation using JSON schema;
    * [Entity validation](#validation) using Symfony's validator;
    * [Create, update, and delete](#creating-updating-and-deleting) out of the box;
    * Assign services to handle any of the above for specific resources.
* [Metadata caching](#caching), similar to that of Doctrine 2;
  * Redis,
  * Or Memcached.

Here's what you'll need.

* A Doctrine 2 entity map;
* A RAML API definition;
* At least one Symfony 2 security voter.

# Try it out

Check out [the example app project](https://github.com/skqr/hateoas-bundle-example), so you can feel the magic in your finger tips without much ado.

___

# Installation

## Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```bash
$ composer require gointegro/hateoas-bundle
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

## Step 2: Enable the Bundle

Then, enable the bundle by adding the following line in the `app/AppKernel.php`
file of your project:

```php
<?php
// app/AppKernel.php

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            new GoIntegro\Bundle\HateoasBundle\GoIntegroHateoasBundle(),
        );
    }
}
?>
```

## Step 3: Add these parameters

```yaml
# app/config/parameters.yml

# HATEOAS API
api.base_url: "http://api.gointegro.com"
api.url_path: "/api/v2"
api.resource_class_path: "Rest2/Resource"
```

## Step 4: Add these routes

```yaml
# app/config/routing.yml

# Place it underneath it all - it contains a catch-all route.
go_integro_hateoas:
    resource: "@GoIntegroHateoasBundle/Resources/config/routing.yml"
    prefix: /api/v2
```

___

# Usage

Check out [the library docs](https://github.com/GoIntegro/hateoas/) for more info.
