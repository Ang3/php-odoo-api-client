PHP Odoo API client documentation
=================================

This package is a PHP library to connect and interact with an Odoo database.
It follows [the official Odoo documentation](https://www.odoo.com/documentation/13.0/developer/misc/api/odoo.html).

Main features
=============

- Built-in ORM methods
- Expression builder for domains and collection operations
- Database Abstraction Layer (DBAL)
  - Query builder
  - Repository
  - Schema

Getting started
===============

Software requirements
---------------------

PHP version 7.2 or newer to develop using the client. Other requirements, such as PHP extensions, are enforced by
composer. See the `require` section of ::composer.json
for details.

The installation of the PHP extension `php-curl` is recommended
for HTTP requests. If missing, PHP native streams are used.

Odoo database support
---------------------

| Odoo series | Compatibility | Comment |
| --- | --- | --- |
| Newer | Unknown | Needs feedbacks |
| v13.0 | Yes | Some Odoo model names changed (e.g account.invoice > account.move) |
| v12.0 | Yes | First tested version |
| Older | Unknown | Needs feedbacks |

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