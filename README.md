PHP Odoo API client
===================

[![Code Quality](https://github.com/ang3/php-odoo-api-client/actions/workflows/php_lint.yml/badge.svg)](https://github.com/ang3/php-odoo-api-client/actions/workflows/php_lint.yml)
[![PHPUnit tests](https://github.com/ang3/php-odoo-api-client/actions/workflows/phpunit.yml/badge.svg)](https://github.com/ang3/php-odoo-api-client/actions/workflows/phpunit.yml)
[![Latest Stable Version](https://poser.pugx.org/ang3/php-odoo-api-client/v/stable)](https://packagist.org/packages/ang3/php-odoo-api-client) 
[![Latest Unstable Version](https://poser.pugx.org/ang3/php-odoo-api-client/v/unstable)](https://packagist.org/packages/ang3/php-odoo-api-client) 
[![Total Downloads](https://poser.pugx.org/ang3/php-odoo-api-client/downloads)](https://packagist.org/packages/ang3/php-odoo-api-client)

This package is a PHP library to connect and interact with an Odoo instance via JSON-RPC by default.
It follows [the official Odoo documentation](https://www.odoo.com/documentation/13.0/developer/misc/api/odoo.html).

> From v8.x, this package is dedicated to the client only and PHP 8.1+.
> 
> - For DBAL features, please report to the package [PHP Odoo DBAL](https://github.com/ang3/php-odoo-dbal).
>
> - For older versions of PHP, please use the version 7.x.

Odoo version support
--------------------

| Odoo series | Compatibility | Comment         |
|-------------|---------------|-----------------|
| v13.0+      | Unknown       | Needs feedbacks |
| v13.0       | Yes           |                 |
| v12.0       | Yes           |                 |
| Older       | Unknown       | Needs feedbacks |

Getting started
===============

Requirements
------------

PHP version 8.1 or newer to develop using the client. Other requirements, such as PHP extensions, are enforced by
composer. See the `require` section of [composer.json file](../composer.json)
for details.

Installation
------------

Open a command console, enter your project directory and execute the
following command to download the latest stable version of the client:

```console
$ composer require ang3/php-odoo-api-client
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Basic usage
-----------

### Create a client

First, create a client instance statically with your config:

```php
<?php

require_once 'vendor/autoload.php';

use Ang3\Component\Odoo\Client;

$client = Client::create([
    'url' => '<url_of_instance>',
    'database' => '<name_of_database>',
    'username' => '<your_username>',
    'password' => '<your_password>',
], $logger = null);
```

Exceptions:
- ```Ang3\Component\Odoo\Exception\MissingConfigParameterException``` when a required parameter is missing 
from the static method ```create()```.

### Make a request

```php
$result = $client->request('service_name', 'method_name', 'argument_1', 'argument_2'/*, ...*/);
```

Exceptions:
- ```Ang3\Component\Odoo\Exception\AuthenticationException``` when authentication failed.
- ```Ang3\Component\Odoo\Exception\RequestException``` when request failed.
- ```Ang3\Component\Odoo\Exception\TransportException``` on transport errors.

These previous exception can be thrown by all methods of the client.

### ExecuteKw

The client has a shortcut for the Odoo method `executeKw` of Odoo service `object`.
By calling the method `executeKw`, the client tries to authenticate then makes the request and returns result.

```php
$result = $client->executeKw('name', 'method', $parameters = [], $options = []);
```

The UID is stored in the client to process authentication once. You can retrieve the UID by calling the client getter:

```php
$uid = $client->getUid(); // int|null
```

### Get the Odoo version

```php
$version = $client->version(); // \Ang3\Component\Odoo\Metadata\Version
dump($version);
```

### Database Abstraction Layer (DBAL)

You need to manage your Odoo database models? 
Please see the package [Odoo DBAL](https://github.com/ang3/php-odoo-dbal) to execute queries like Doctrine.

That's it!