PHP Odoo API client
===================

[![Build Status](https://travis-ci.org/Ang3/php-odoo-api-client.svg?branch=master)](https://travis-ci.org/Ang3/php-odoo-api-client) [![Latest Stable Version](https://poser.pugx.org/ang3/php-odoo-api-client/v/stable)](https://packagist.org/packages/ang3/php-odoo-api-client) [![Latest Unstable Version](https://poser.pugx.org/ang3/php-odoo-api-client/v/unstable)](https://packagist.org/packages/ang3/php-odoo-api-client) [![Total Downloads](https://poser.pugx.org/ang3/php-odoo-api-client/downloads)](https://packagist.org/packages/ang3/php-odoo-api-client)

Odoo External API client. See [documentation](https://www.odoo.com/documentation/12.0/webservices/odoo.html) for more information.

Installation
============

Step 1: Download sources
------------------------

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require ang3/php-odoo-api-client
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Usage
=====

```php
<?php

require_once 'vendor/autoload.php';

use Ang3\Component\Odoo\Client\ExternalApiClient;

$client = new ExternalApiClient('<host>', '<database>', '<user>', '<password>');

var_dump($client->version());
var_dump($client->getUid());
var_dump($client->search('res.company'));
var_dump($client->read('res.company', [2]));

```

That's it!

Upgrades
========

### From 2.* to 3.*

- Updated namespace ```Ang3\Component\OdooApiClient``` to ```Ang3\Component\Odoo\Client```

ORM project
===========

Looking for more advanced features? Look at [there](https://github.com/Ang3/php-odoo-orm).