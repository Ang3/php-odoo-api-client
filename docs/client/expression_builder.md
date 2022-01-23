Expression builder
==================

There are two kinds of expressions : ```domains``` for criteria
and ```collection operations``` in data writing context.
Odoo has its own array format for those expressions.
The aim of the expression builder is to provide some
helper methods to simplify your programmer's life.

Here is an example of how to get a builder from the client:

```php
$expr = $client->getExpressionBuilder();
```

You can still use the expression builder as standalone by creating a new instance:

```php
use Ang3\Component\Odoo\Expression\ExpressionBuilder;

$expr = new ExpressionBuilder();
```

Domains
-------

For all **select/search/count** queries,
Odoo is waiting for an array of [domains](https://www.odoo.com/documentation/13.0/reference/orm.html#search-domains)
with a *polish notation* for logical operations (```AND```, ```OR``` and ```NOT```).

It could be quickly ugly to do a complex domain, but don't worry the builder makes all
for you. :-)

Each domain builder method creates an instance of ```Ang3\Component\Odoo\Expression\Domain\DomainInterface```.
The only one method of this interface is ```toArray()``` to get a normalized array of the expression.

To illustrate how to work with it, here is an example using ```ExpressionBuilder``` helper methods:

```php
// Get the expression builder
$expr = $client->expr();

$result = $client->findBy('model_name', $expr->andX( // Logical node "AND"
	$expr->gte('id', 10), // id >= 10
	$expr->lte('id', 100), // id <= 10
));
```

Of course, you can nest logical nodes:

```php
$result = $client->findBy('model_name', $expr->andX(
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

In data writing context with queries of type ```insert``` or ```update```, 
Odoo allows you to manage ***toMany** collection fields with special commands.

Please read the [ORM documentation](https://www.odoo.com/documentation/13.0/reference/orm.html#openerp-models-relationals-format)
to get more information about.

The expression builder provides helper methods to build a well-formed *operation command*:
each operation method returns an instance of ```Ang3\Component\Odoo\Expression\Operation\CollectionOperation```.
Like domains, the only one method of this interface is ```toArray()``` to get a normalized array of the expression.

To illustrate how to work with operations, here is an example using ```ExpressionBuilder``` helper methods:

```php
// Get the expression builder
$expr = $client->expr();

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

$result = $client->create('model_name', $data);
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