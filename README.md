PHP Odoo API client
===================

[![Build Status](https://travis-ci.org/Ang3/php-odoo-api-client.svg?branch=master)](https://travis-ci.org/Ang3/php-odoo-api-client) 
[![Latest Stable Version](https://poser.pugx.org/ang3/php-odoo-api-client/v/stable)](https://packagist.org/packages/ang3/php-odoo-api-client) 
[![Latest Unstable Version](https://poser.pugx.org/ang3/php-odoo-api-client/v/unstable)](https://packagist.org/packages/ang3/php-odoo-api-client) 
[![Total Downloads](https://poser.pugx.org/ang3/php-odoo-api-client/downloads)](https://packagist.org/packages/ang3/php-odoo-api-client)

Odoo API client using 
[XML-RPC Odoo ORM External API](https://www.odoo.com/documentation/12.0/webservices/odoo.html). It allows 
you call your odoo instance and manage records easily.

**You are reading the documentation of version ```7.0```, if your version is older, please read
[this documentation (6.1.3)](https://github.com/Ang3/php-odoo-api-client/tree/v6.1.3).**

**Main features**

- Authentication ```<7.0```
- Basic XML-RPC calls ```<7.0```
- Expression builder  ```<7.0```
- Database Abstraction Layer (DBAL) ```>=7.0```
  - Record manager 
  - Repositories`
  - Query builder

**Good to know**

The ORM (Object relational mapper) is in development: 
[ang3/php-odoo-orm](https://github.com/Ang3/php-odoo-orm) (need tests). 

If you are in Symfony application you should be interested in the bundle 
[ang3/odoo-bundle](https://github.com/Ang3/odoo-bundle) (client and ORM integration - need tests).

Requirements
============

- The PHP extension ```php-xmlrpc``` must be enabled.

| Odoo server | Compatibility | Comment |
| --- | --- | --- |
| newer | Unknown | Needs feddback |
| v13.0 | Yes | Some Odoo model names changed (e.g account.invoice > account.move) |
| v12.0 | Yes | First tested version |
| < v12 | Unknown | Needs feddback |

Installation
============

Open a command console, enter your project directory and execute the
following command to download the latest stable version of the client:

```console
$ composer require ang3/php-odoo-api-client
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Basic usage
===========

First, you have to create a client instance:

```php
<?php

require_once 'vendor/autoload.php';

use Ang3\Component\Odoo\Client;

// Option 1: by calling the constructor...
$client = new Client('<host>', '<database>', '<username>', '<password>', $logger = null);

// Option 2 : by calling the static method ::createFromConfig() with configuration as array
$client = Client::createFromConfig([
    'url' => '<host>',
    'database' => '<database>',
    'username' => '<user>',
    'password' => '<password>',
], $logger = null);
```

Exceptions:
- ```Ang3\Component\Odoo\Exception\MissingConfigParameterException``` when a required parameter is missing 
from the static method ```createFromConfig()```.

Then, make your call:

```php
$result = $client->call($name, $method, $parameters = [], $options = []);
```

Exceptions:
- ```Ang3\Component\Odoo\Exception\AuthenticationException``` when authentication failed.
- ```Ang3\Component\Odoo\Exception\RequestException``` when request failed.

These previous exception can be thrown by all methods of the client.

DBAL (Database Abstraction Layer)
=================================

First of all, Odoo is a database. Each "model" is a table and has its own fields.

> DBAL features was added in version ```7.0``` - If your version is older, please use the built-in 
ORM methods of the client like explained in the 
> [dedicated documentation](https://github.com/Ang3/php-odoo-api-client/tree/v6.1.3):
be aware that these client ORM methods are deprecated since version ```7.0```.

Record manager
--------------

The client provides a record manager to manage records of your Odoo models.

You can get the related manager of the client like below:

```php
$recordManager = $client->getRecordManager();
```

You can also create your own with a client instance:

```php
use Ang3\Component\Odoo\DBAL\RecordManager;

/** @var \Ang3\Component\Odoo\Client $myClient */
$recordManager = new RecordManager($myClient);
```

### Built-in ORM methods

Here is all built-in ORM methods provided by the record manager:

```php
use Ang3\Component\Odoo\DBAL\Expression\DomainInterface;

/**
 * Create a new record.
 *
 * @return int the ID of the new record
 */public function create(string $modelName, array $data): int;

/**
 * Update record(s).
 *
 * NB: It is not currently possible to perform “computed” updates (by criteria).
 * To do it, you have to perform a search then an update with search result IDs.
 *
 * @param array|int $ids
 */
public function update(string $modelName, $ids, array $data = []): void;

/**
 * Delete record(s).
 *
 * NB: It is not currently possible to perform “computed” deletes (by criteria).
 * To do it, you have to perform a search then a delete with search result IDs.
 *
 * @param array|int $ids
 */
public function delete(string $modelName, $ids): void;

/**
 * Search one ID of record by criteria.
 */
public function searchOne(string $modelName, ?DomainInterface $criteria): ?int;

/**
 * Search all ID of record(s).
 *
 * @return int[]
 */
public function searchAll(string $modelName, array $orders = [], int $limit = null, int $offset = null): array;

/**
 * Search ID of record(s) by criteria.
 *
 * @return int[]
 */
public function search(string $modelName, ?DomainInterface $criteria = null, array $orders = [], int $limit = null, int $offset = null): array;

/**
 * Find ONE record by ID.
 *
 * @throws RecordNotFoundException when the record was not found
 */
public function read(string $modelName, int $id, array $fields = []): array;

/**
 * Find ONE record by ID.
 */
public function find(string $modelName, int $id, array $fields = []): ?array;

/**
 * Find ONE record by criteria.
 */
public function findOneBy(string $modelName, ?DomainInterface $criteria = null, array $fields = [], array $orders = [], int $offset = null): ?array;

/**
 * Find all records.
 *
 * @return array[]
 */
public function findAll(string $modelName, array $fields = [], array $orders = [], int $limit = null, int $offset = null): array;

/**
 * Find record(s) by criteria.
 *
 * @return array[]
 */
public function findBy(string $modelName, ?DomainInterface $criteria = null, array $fields = [], array $orders = [], int $limit = null, int $offset = null): array;

/**
 * Check if a record exists.
 */
public function exists(string $modelName, int $id): bool;

/**
 * Count number of all records for the model.
 */
public function countAll(string $modelName): int;

/**
 * Count number of records for a model and criteria.
 */
public function count(string $modelName, ?DomainInterface $criteria = null): int;
```

For ```$criteria``` in select/search queries and ```$data``` for data writing context, please read the section 
[Expression builder](#expression-builder).

Schema
------

You can get the schema of your Odoo database by calling the getter method
```RecordManager::getSchema()```:

```php
/** @var \Ang3\Component\Odoo\DBAL\Schema\Schema $schema */
$schema = $recordManager->getSchema();
```

The schema helps you to get all model names or get metadata of a model.

### Get all model names

```php
/** @var string[] $modelNames */
$modelNames = $schema->getModelNames();
```

### Get model metadata

```php
/** @var \Ang3\Component\Odoo\DBAL\Schema\Model $model */
$model = $schema->getModel('res.company');
```

An exception of type ```Ang3\Component\Odoo\DBAL\Schema\SchemaException``` is thrown if the model
does not exist.

Query builder
-------------

It helps you to create queries easily by chaining helpers methods (like Doctrine for SQL databases).

### Create a query builder

```php
/** @var string|null $modelName */
$queryBuilder = $recordManager->createQueryBuilder($modelName);
```

The variable ```$modelName``` represents the target model of your query (clause ```from```).

### Build your query

Here is a complete list of helper methods available in ```QueryBuilder```:

```php
/**
 * Defines the query of type "SELECT" with selected fields.
 * No fields selected = all fields returned.
 *
 * @param array|string|null $fields
 */
public function select($fields = null): self;

/**
 * Defines the query of type "SEARCH".
 */
public function search(): self;

/**
 * Defines the query of type "INSERT".
 */
public function insert(): self;

/**
 * Defines the query of type "UPDATE" with ids of records to update.
 *
 * @param int[] $ids
 */
public function update(array $ids): self;

/**
 * Defines the query of type "DELETE" with ids of records to delete.
 */
public function delete(array $ids): self;

/**
 * Adds a field to select.
 *
 * @throws LogicException when the type of the query is not "SELECT".
 */
public function addSelect(string $fieldName): self;

/**
 * Gets selected fields.
 */
public function getSelect(): array;

/**
 * Sets the target model name.
 */
public function from(string $modelName): self;

/**
 * Gets the target model name of the query.
 */
public function getFrom(): ?string;

/**
 * Sets target IDs in case of query of type "UPDATE" or "DELETE".
 *
 * @throws LogicException when the type of the query is not "UPDATE" nor "DELETE".
 */
public function setIds(array $ids): self;

/**
 * Adds target ID in case of query of type "UPDATE" or "DELETE".
 *
 * @throws LogicException when the type of the query is not "UPDATE" nor "DELETE".
 */
public function addId(int $id): self;

/**
 * Sets field values in case of query of type "INSERT" or "UPDATE".
 *
 * @throws LogicException when the type of the query is not "INSERT" nor "UPDATE".
 */
public function setValues(array $values = []): self;

/**
 * Set a field value in case of query of type "INSERT" or "UPDATE".
 *
 * @param mixed $value
 *
 * @throws LogicException when the type of the query is not "INSERT" nor "UPDATE".
 */
public function set(string $fieldName, $value): self;

/**
 * Gets field values set in case of query of type "INSERT" or "UPDATE".
 */
public function getValues(): array;

/**
 * Sets criteria for queries of type "SELECT" and "SEARCH".
 *
 * @throws LogicException when the type of the query is not "SELECT" not "SEARCH".
 */
public function where(?DomainInterface $domain = null): self;

/**
 * Takes the WHERE clause and adds a node with logical operator AND.
 *
 * @throws LogicException when the type of the query is not "SELECT" nor "SEARCH".
 */
public function andWhere(DomainInterface $domain): self;

/**
 * Takes the WHERE clause and adds a node with logical operator OR.
 *
 * @throws LogicException when the type of the query is not "SELECT" nor "SEARCH".
 */
public function orWhere(DomainInterface $domain): self;

/**
 * Gets the WHERE clause.
 */
public function getWhere(): ?DomainInterface;

/**
 * Sets orders.
 */
public function setOrders(array $orders = []): self;

/**
 * Clears orders and adds one.
 */
public function orderBy(string $fieldName, bool $isAsc = true): self;

/**
 * Adds order.
 *
 * @throws LogicException when the query type is not valid.
 */
public function addOrderBy(string $fieldName, bool $isAsc = true): self;

/**
 * Gets ordered fields.
 */
public function getOrders(): array;

/**
 * Sets the max results of the query (limit).
 */
public function setMaxResults(?int $maxResults): self;

/**
 * Gets the max results of the query.
 */
public function getMaxResults(): ?int;

/**
 * Sets the first results of the query (offset).
 */
public function setFirstResult(?int $firstResult): self;

/**
 * Gets the first results of the query.
 */
public function getFirstResult(): ?int;
```

Then, build your query like below:

```php
$query = $queryBuilder->getQuery();
```

Your query is an instance of ```Ang3\Component\Odoo\Query\OrmQuery```.

### Execute your query

You can get/count results or execute insert/update/delete by differents ways depending on the query type.

```php
/**
 * Counts the number of records from parameters.
 * Allowed methods: SEARCH, SEARCH_READ.
 *
 * @throws QueryException on invalid query method.
 */
public function count(): int;

/**
 * Gets just ONE scalar result.
 * Allowed methods: SEARCH, SEARCH_READ.
 *
 * @return bool|int|float|string
 *
 * @throws NoUniqueResultException on no unique result
 * @throws NoResultException       on no result
 * @throws QueryException          on invalid query method.
 */
public function getSingleScalarResult();

/**
 * Gets one or NULL scalar result.
 * Allowed methods: SEARCH, SEARCH_READ.
 *
 * @return bool|int|float|string|null
 *
 * @throws NoUniqueResultException on no unique result
 * @throws QueryException          on invalid query method.
 */
public function getOneOrNullScalarResult();

/**
 * Gets a list of scalar result.
 * Allowed methods: SEARCH, SEARCH_READ.
 *
 * @throws QueryException on invalid query method.
 *
 * @return array<bool|int|float|string>
 */
public function getScalarResult(): array;

/**
 * Gets one row.
 * Allowed methods: SEARCH, SEARCH_READ.
 *
 * @throws NoUniqueResultException on no unique result
 * @throws NoResultException       on no result
 * @throws QueryException          on invalid query method.
 */
public function getSingleResult(): array;

/**
 * Gets one or NULL row.
 * Allowed methods: SEARCH, SEARCH_READ.
 *
 * @throws NoUniqueResultException on no unique result
 * @throws QueryException          on invalid query method.
 */
public function getOneOrNullResult(): ?array;

/**
 * Gets all result rows.
 * Allowed methods: SEARCH, SEARCH_READ.
 *
 * @throws QueryException on invalid query method.
 */
public function getResult(): array;

/**
 * Execute the query.
 * Allowed methods: all.
 *
 * @return mixed
 */
public function execute();
```

Repositories
============

Sometimes, you would want to keep your queries in memory to reuse it in your code. To do it, you should use 
a repository. A repository is a class that helps you to isolate queries for a dedicated model.

For example, let's create the repository for your companies and define a query to get all french companies:

```php
namespace App\Odoo\Repository;

use Ang3\Component\Odoo\DBAL\RecordManager;
use Ang3\Component\Odoo\DBAL\Repository\RecordRepository;

class CompanyRepository extends RecordRepository
{
    public function __construct(RecordManager $recordManager)
    {
        parent::__construct($recordManager, 'res.company');
    }

    public function findFrenchCompanies(): array
    {
        return $this
            ->createQueryBuilder()
            ->select('name')
            ->where($this->expr()->eq('country_id.code', 'FR'))
            ->getQuery()
            ->getResult();
    }
}
```

Note that Odoo will always return the record ID in the result, even if you didn't select it explicitly.

Each repository is registered inside the record manager on construct.
That's why you can retrieve your repository directly from the record manager:

```php
/** @var \App\Odoo\Repository\CompanyRepository $companyRepository */
$companyRepository = $recordManager->getRepository('res.company');
```

If no repository exists for a model, the default repository ```Ang3\Component\Odoo\DBAL\Repository\RecordRepository``` 
is used. Last but not least, all repositories are stored into the related record manager to avoid creating multiple 
instances of same repository.

Expression builder
==================

There are two kinds of expressions : ```domains``` for criteria
and ```collection operations``` in data writing context.
Odoo has its own array format for those expressions.
The aim of the expression builder is to provide some
helper methods to simplify your programmer's life.

Here is an example of how to get a builder from a client or record manager:

```php
$expr = $clientOrRecordManager->expr();
// or $expr = $clientOrRecordManager->getExpressionBuilder();
```

You can still use the expression builder as standalone by creating a new instance:

```php
use Ang3\Component\Odoo\DBAL\Expression\ExpressionBuilder;

$expr = new ExpressionBuilder();
```

Domains
-------

For all **select/search/count** queries,
Odoo is waiting for an array of [domains](https://www.odoo.com/documentation/13.0/reference/orm.html#search-domains)
with a *polish notation* for logical operations (```AND```, ```OR``` and ```NOT```).

It could be quickly ugly to do a complex domain, but don't worry the builder makes all
for you. :-)

Each domain builder method creates an instance of ```Ang3\Component\Odoo\Expression\DomainInterface```.
The only one method of this interface is ```toArray()``` to get a normalized array of the expression.

To illustrate how to work with it, here is an example using ```ExpressionBuilder``` helper methods:

```php
// Get the expression builder
$expr = $recordManager->expr();

$result = $recordManager->findBy('model_name', $expr->andX( // Logical node "AND"
	$expr->gte('id', 10), // id >= 10
	$expr->lte('id', 100), // id <= 10
));
```

Of course, you can nest logical nodes:

```php
$result = $recordManager->findBy('model_name', $expr->andX(
    $expr->orX(
        $expr->eq('A', 1),
        $expr->eq('B', 1)
    ),
    $expr->orX(
        $expr->eq('C', 1),
        $expr->eq('D', 1),
        $expr->eq('E', 1)
    )
));
```

Internally, the client formats automatically all domains by calling the special builder
method ```normalizeDomains()```.

Here is a complete list of helper methods available in ```ExpressionBuilder``` for domain expressions:

```php
/**
 * Create a logical operation "AND".
 */
public function andX(DomainInterface ...$domains): CompositeDomain;

/**
 * Create a logical operation "OR".
 */
public function orX(DomainInterface ...$domains): CompositeDomain;

/**
 * Create a logical operation "NOT".
 */
public function notX(DomainInterface ...$domains): CompositeDomain;

/**
 * Check if the field is EQUAL TO the value.
 *
 * @param mixed $value
 */
public function eq(string $fieldName, $value): Comparison;

/**
 * Check if the field is NOT EQUAL TO the value.
 *
 * @param mixed $value
 */
public function neq(string $fieldName, $value): Comparison;

/**
 * Check if the field is UNSET OR EQUAL TO the value.
 *
 * @param mixed $value
 */
public function ueq(string $fieldName, $value): Comparison;

/**
 * Check if the field is LESS THAN the value.
 *
 * @param mixed $value
 */
public function lt(string $fieldName, $value): Comparison;

/**
 * Check if the field is LESS THAN OR EQUAL the value.
 *
 * @param mixed $value
 */
public function lte(string $fieldName, $value): Comparison;

/**
 * Check if the field is GREATER THAN the value.
 *
 * @param mixed $value
 */
public function gt(string $fieldName, $value): Comparison;

/**
 * Check if the field is GREATER THAN OR EQUAL the value.
 *
 * @param mixed $value
 */
public function gte(string $fieldName, $value): Comparison;

/**
 * Check if the variable is LIKE the value.
 *
 * An underscore _ in the pattern stands for (matches) any single character
 * A percent sign % matches any string of zero or more characters.
 *
 * If $strict is set to FALSE, the value pattern is "%value%" (automatically wrapped into signs %).
 *
 * @param mixed $value
 */
public function like(string $fieldName, $value, bool $strict = false, bool $caseSensitive = true): Comparison;

/**
 * Check if the field is IS NOT LIKE the value.
 *
 * @param mixed $value
 */
public function notLike(string $fieldName, $value, bool $caseSensitive = true): Comparison;

/**
 * Check if the field is IN values list.
 */
public function in(string $fieldName, array $values = []): Comparison;

/**
 * Check if the field is NOT IN values list.
 */
public function notIn(string $fieldName, array $values = []): Comparison;
```

Collection operations
---------------------

In data writing context with queries of type **insert/update**, Odoo allows you to manage ***toMany** collection
fields with special commands.

Please read the [ORM documentation](https://www.odoo.com/documentation/13.0/reference/orm.html#openerp-models-relationals-format)
to known what we are talking about.

The expression builder provides helper methods to build a well-formed *operation command*:
each operation method returns an instance of ```Ang3\Component\Odoo\DBAL\Expression\CollectionOperation```.
Like domains, the only one method of this interface is ```toArray()``` to get a normalized array of the expression.

To illustrate how to work with operations, here is an example using ```ExpressionBuilder``` helper methods:

```php
// Get the expression builder
$expr = $recordManager->expr();

// Prepare data for a new record
$data = [
    'foo' => 'bar',
    'bar_ids' => [ // Field of type "manytoMany"
        $expr->addRecord(3), // Add the record of ID 3 to the set
        $expr->createRecord([  // Create a new sub record and add it to the set
            'bar' => 'baz'
            // ...
        ])
    ]
];

$result = $recordManager->create('model_name', $data);
```

Internally, the client formats automatically the whole query parameters for all writing methods
(```create``` and ```update```) by calling the special builder
method ```normalizeData()```.

Here is a complete list of helper methods available in ```ExpressionBuilder``` for operation expressions:

```php
/**
 * Adds a new record created from data.
 */
public function createRecord(array $data): CollectionOperation;

/**
 * Updates an existing record of id $id with data.
 * /!\ Can not be used in record CREATE query.
 */
public function updateRecord(int $id, array $data): CollectionOperation;

/**
 * Adds an existing record of id $id to the collection.
 */
public function addRecord(int $id): CollectionOperation;

/**
 * Removes the record of id $id from the collection, but does not delete it.
 * /!\ Can not be used in record CREATE query.
 */
public function removeRecord(int $id): CollectionOperation;

/**
 * Removes the record of id $id from the collection, then deletes it from the database.
 * /!\ Can not be used in record CREATE query.
 */
public function deleteRecord(int $id): CollectionOperation;

/**
 * Replaces all existing records in the collection by the $ids list,
 * Equivalent to using the command "clear" followed by a command "add" for each id in $ids.
 */
public function replaceRecords(array $ids = []): CollectionOperation;

/**
 * Removes all records from the collection, equivalent to using the command "remove" on every record explicitly.
 * /!\ Can not be used in record CREATE query.
 */
public function clearRecords(): CollectionOperation;
```

Data support
------------

- Scalar values are unchanged
- Arrays recursive conversion
- Objects of type ```\DateTimeInterface``` are automatically formatted into string in UTC timezone
- Iterable/generator are fetched into an array
- Non-iterable values are automatically casted to string
  (so any non-supported objects must define the method ```__toString()```)

That's it!

___

Resources
=========

- [CHANGELOG.md](CHANGELOG.md)