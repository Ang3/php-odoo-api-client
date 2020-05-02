Built-in methods
================

ORM methods
-----------

For all these methods, all previous client exceptions can be thrown too.

**Create record**

```php
$data = [
    'field_name' => 'value'
];

$recordId = $client->create('model_name', $data); // int
```

To manage data collection fields, you can build operations with the 
[Expression Builder](#expression-builder).

**Read records**

```php
$ids = [1,2,3]; // Or $ids = 1 (array<int>|int)
$records = $client->read('model_name', $ids); // array
```

**Update records**

```php
$ids = [1,2,3]; // Or $ids = 1 (array<int>|int)
$data = [
    'field_name' => 'value'
];

$client->update('model_name', $ids, $data); // void
```

To manage data collection fields, you can build operations with the 
[Expression Builder](#expression-builder).

**Delete records**

```php
$ids = [1,2,3]; // Or $ids = 1 (array<int>|int)
$client->delete('model_name', $ids); // void
```

**Search records**

Get the ID of matched record(s).

```php
$criteria = [[['id', '=', 18]]];
$recordIds = $client->search('model_name', $criteria = null, $options = []); // array<int>
```

The variable ```$criteria``` can be ```NULL```, an array or directly a domain expression from the 
[Expression Builder](#expression-builder).

**Find ONE record by ID**

```php
$record = $client->find('model_name', 1, $options = []); // array or NULL
```

**Find ONE record by criteria and options**

```php
$record = $client->findOneBy('model_name', $criteria = null, $options = []); // array or NULL
```

The variable ```$criteria``` can be ```NULL```, an array or directly a domain expression from the 
[Expression Builder](#expression-builder).

**Find records by criteria and options**

```php
$criteria = [[['id', '=', 18]]];
$records = $client->findBy('model_name', $criteria = null, $options = []); // array<array>
```

The variable ```$criteria``` can be ```NULL```, an array or directly a domain expression from the 
[Expression Builder](#expression-builder).

**Count records**

```php
$criteria = [[['id', '=', 18]]];
$nbRecords = $client->count('model_name', $criteria = null); // int
```

The variable ```$criteria``` can be ```NULL```, an array or directly a domain expression from the 
[Expression Builder](#expression-builder).

**List record fields**

```php
$fields = $client->listFields('model_name', $options = []); // array<array>
```

Miscellaneous
-------------

**Authenticate - Get the UUID**

```php
$uuid = $client->authenticate(); // string
```

**Version of Odoo server**

```php
$version = $client->version(); // (array)
```

Links
-----

- **Next** > Create complex domains and manage collection fields easily with the [Expression Builder](docs/expression_builder.md).
- **Back** to [Index](https://github.com/Ang3/php-odoo-api-client)