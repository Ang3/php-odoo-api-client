PHP Odoo API client
===================

This package is a PHP library to connect and interact with an Odoo instance via JSON-RPC by default.
It follows [the official Odoo documentation](https://www.odoo.com/documentation/13.0/developer/misc/api/odoo.html).

Getting started
===============

Requirements
------------

PHP version 8.1 or newer to develop using the client. Other requirements, such as PHP extensions, are enforced by
composer. See the `require` section of [composer.json file](../composer.json)
for details.

Odoo database support
---------------------

| Odoo series | Compatibility | Comment                                                            |
|-------------|---------------|--------------------------------------------------------------------|
| Newer       | Unknown       | Needs feedbacks                                                    |
| v13.0       | Yes           | Some Odoo model names changed (e.g account.invoice > account.move) |
| v12.0       | Yes           | First tested version                                               |
| Older       | Unknown       | Needs feedbacks                                                    |

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

First, import the client with a `use` statement and create a client instance statically 
with your config:

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