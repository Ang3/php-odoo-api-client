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

First, you have to create a client instance:

```php
<?php

require_once 'vendor/autoload.php';

use Ang3\Component\Odoo\ExternalApiClient;

// Create instance...
$client = new ExternalApiClient('<host>', '<database>', '<user>', '<password>' /*, $options = []*/);

// You can create client from array parameters
$client = ExternalApiClient::createFromArray([
	'host' => '<host>',
	'database' => '<database>',
	'user' => '<user>',
	'password' => '<password>',
], /*, $options = []*/);

```

### ORM

#### Create record

```php
$recordId = $client->create('res.company', $fields = []);
```

#### Search records

```php
$records = $client->search('res.company');
```

#### Read records

```php
$records = $client->read('res.company', [2]);
```

#### Search and read records

```php
$records = $client->searchAndRead('res.company', $options = []);
```

#### Update records

```php
$client->read('res.company', [2, 3], [
	'display_name' => 'foo'
]);
```

#### Count records

```php
$nbRecords = $client->count('res.company', $parameters = [], $options = []);
```

#### Delete records

```php
$client->delete('res.company', [2, 3]);
```

#### List record fields

```php
$fields = $client->listFields('res.company', $options = []);
```

### Miscellaneous

#### Get the UUID

```php
$uuid = $client->getUid(); // (string)
```

#### Get the version

```php
$version = $client->version(); // (string)
```

Upgrades
========

### From 2.* to 3.*

- Updated namespace ```Ang3\Component\OdooApiClient``` to ```Ang3\Component\Odoo\Client```

### From 3.* to 4.*

- Updated namespace ```Ang3\Component\Odoo\Client``` to ```Ang3\Component\Odoo```

That's it!