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

use Ang3\Component\Odoo\Client;

// Create instance...
$client = new Client([
    'host' => '<host>',
    'database' => '<database>',
    'user' => '<user>',
    'password' => '<password>',
 ]);
```

### Built-in methods

**Create record**

```php
$data = [
    'field_name' => 'value'
];

$recordId = $client->create('res.company', $data);
```

**Read records**

```php
$ids = [1,2,3]; // Or $ids = 1 (array<int>|int)

$records = $client->read('res.company', $ids);
```

**Update records**

```php
$ids = [1,2,3]; // Or $ids = 1 (array<int>|int)

$data = [
    'field_name' => 'value'
];

$client->read('res.company', $ids, $data);
```

**Delete records**

```php
$ids = [1,2,3]; // Or $ids = 1 (array<int>|int)

$client->delete('res.company', $ids);
```

**Search records**

Get the ID of matched record(s).

```php
$records = $client->search('res.company');
```

**Find records**

```php
$records = $client->find('res.company', $criteria = null, $options = []);
```

**Count records**

```php
$nbRecords = $client->count('res.company', $criteria = null, $options = []);
```

**List record fields**

```php
$fields = $client->listFields('res.company', $options = []);
```

### Expression builder

**Added in v.5.0**

Documentation in progress! :)

### Miscellaneous

#### Get the UUID

```php
$uuid = $client->getUid(); // (string)
```

#### Get the version

```php
$version = $client->version(); // (array)
```

Upgrades
========

### From 4.* to 5.*

- Deleted static method ```Client::createFromConfig(array $config)```. Use ```new Client(array $config)``` instead.
- Renamed method ```searchAndRead(...)``` to ```find(...)```
- Added expression builder support.

### From 3.* to 4.*

- Updated namespace ```Ang3\Component\Odoo\Client``` to ```Ang3\Component\Odoo```

### From 2.* to 3.*

- Updated namespace ```Ang3\Component\OdooApiClient``` to ```Ang3\Component\Odoo\Client```

That's it!