[![Build Status](https://travis-ci.org/josegonzalez/cakephp-dynamic-route.png?branch=master)](https://travis-ci.org/josegonzalez/cakephp-dynamic-route) [![Coverage Status](https://coveralls.io/repos/josegonzalez/cakephp-dynamic-route/badge.png?branch=master)](https://coveralls.io/r/josegonzalez/cakephp-dynamic-route?branch=master) [![Total Downloads](https://poser.pugx.org/josegonzalez/cakephp-dynamic-route/d/total.png)](https://packagist.org/packages/josegonzalez/cakephp-dynamic-route) [![Latest Stable Version](https://poser.pugx.org/josegonzalez/cakephp-dynamic-route/v/stable.png)](https://packagist.org/packages/josegonzalez/cakephp-dynamic-route)

# DynamicRoute Plugin (For CakePHP 2.0)

Read routes from a database into `routes.php` quickly and easily

## Background

As with the other route class I built, someone asked in IRC if it would be possible to read the routes from the database and load them into the `app/Config/routes.php` file on the fly. I decided this was true, and this is the result.

## Requirements

* PHP 5.2+
* CakePHP 2.0

## Installation

_[Using [Composer](http://getcomposer.org/)]_

Add the plugin to your project's `composer.json` - something like this:

    {
        "require": {
            "josegonzalez/cakephp-dynamic-route": "1.0.0"
        }
    }

Because this plugin has the type `cakephp-plugin` set in it's own `composer.json`, composer knows to install it inside your `/Plugins` directory, rather than in the usual vendors file. It is recommended that you add `/Plugins/DynamicRoute` to your .gitignore file. (Why? [read this](http://getcomposer.org/doc/faqs/should-i-commit-the-dependencies-in-my-vendor-directory.md).)

_[Manual]_

* Download this: [https://github.com/josegonzalez/cakephp-dynamic-route/zipball/master](https://github.com/josegonzalez/cakephp-dynamic-route/zipball/master)
* Unzip that download.
* Copy the resulting folder to `app/Plugin`
* Rename the folder you just copied to `DynamicRoute`

_[GIT Submodule]_

In your app directory type:

    git submodule add git://github.com/josegonzalez/cakephp-dynamic-route.git Plugin/DynamicRoute
    git submodule init
    git submodule update

_[GIT Clone]_

In your plugin directory type

    git clone git://github.com/josegonzalez/cakephp-dynamic-route.git DynamicRoute

### Enable plugin

In 2.0 you need to enable the plugin your `app/Config/bootstrap.php` file:

        CakePlugin::load('DynamicRoute');

If you are already using `CakePlugin::loadAll();`, then this is not necessary.

## Usage

Way near the bottom of your `app/Config/routes.php` file, before the line where the default routes are loaded, add the following:

```php
App::uses('FancyRoute', 'DynamicRoute.Lib');
FancyRoute::connectFancyRoutes();
```

You can now remove all other **hacks** from your `app/Config/routes.php` file.

What we've enabled is creating `spec|slug` routes in the database. A `spec` would be the internal CakePHP mapping, like `posts/view?id=45` or `events/calendar?date=2011-11-01&category=lol`, while the corresponding `slugs` might be something like `/why-isnt-this-pup-asleep` or `/manchester/cakephp-developers-dance-to-beyonce`.

This `spec|slug` system allows one to have a specialized table for routing - by default the `dynamic_routes` table - which can be used across multiple models and controllers if necessary. It allows a developer to create a simple interface for building internal application routes that a non-developer can use at a later date. This is extremely useful when building content management systems that need a Joomla or Wordpress-like routing system.

## Options

`FancyRoute::connectFancyRoutes()` takes an optional array for configuration the route loading:

- `model`: String or Object referencing a CakePHP Model to use for loading records.
  - Default: (string) `DynamicRoute.DynamicRoute`
  - Note: Model being loaded must have a `load` custom find method that returns specs mapping to slugs
- `cacheKey`: Key used for caching the dynamic routes to disk
  - Default: (string) `dynamic_routes`
- `cache`: Whether to cache the db queries
  - Default: (boolean) true

## Notes

Because of the way in which this class works, it is not necessary to call `Router::connect()` on any of the dynamic routes, as this is called internally by the `FancyRoute` class.

The `DynamicRoute` model contains methods for turning a given specification into an internal cakephp request, whether that be a string or array.

You will currently have to create valid `spec|slug` records yourself. Slugs should be prepended with a `/` character, but should not be followed by one. Specs are regular web requests, `$_GET` style.

There is a helper method on the `DynamicRoute` model called `saveNew()`; This method takes a `spec` and `slug`, or an array of data, and returns whether or not the save is successful. It will also do it's best to ensure that the data is properly setup by normalizing specifications and ensuring there slug is in the correct format.

## Todo

- Unit Tests
- Backend UI for creating new dynamic routes
- ~Helper Model method for creating new dynamic routes~
- ~Port to 2.0~

## License

Copyright (c) 2011 Jose Diaz-Gonzalez

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
