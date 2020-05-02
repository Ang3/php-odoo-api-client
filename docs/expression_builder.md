Expression Builder
==================

**This feature was added in version 5.0**

Odoo has its own array format for criteria or collection operations. 

Each builder method creates an instance of ```Ang3\Component\Odoo\Expression\ExpressionInterface```. 
The only one method of this interface is ```toArray()``` in order to get a normalized array of the expression.

Get started
-----------

Here is an example of how to build a ```ExpressionBuilder``` object:

```php
$expr = $client->getExpressionBuilder();
// Or $expr = $client->expr();
```

You can use the expression builder as standalone by creating the instance it yourself.

```php
use Ang3\Component\Odoo\Expression\ExpressionBuilder;

$expr = new ExpressionBuilder();
```

There are two kinds of expressions (methods) : 
- ```Domains``` for criteria
- ```Operations``` for record collection management

Working with the expression builder
-----------------------------------

The most straightforward way to build a dynamic domain with the ```ExpressionBuilder``` 
is by taking advantage of Helper methods. 
For all base code, there is a set of useful methods to simplify a programmer's life. 

To illustrate how to work with them, here is an example using ```ExpressionBuilder``` helper method:

```php
// $client instanceof Client

// Get the expression builder
$expr = $client->expr();

$result = $client->findBy('model_name', $expr->andX(
	$expr->gte('id', 10),
	$expr->lte('id', 100),
), $options = []);
```

Domains
-------

For all **search** queries (```search```, ```findBy```, ```findOneBy``` and ```count```), 
Odoo is waiting for an array of [domains](https://www.odoo.com/documentation/13.0/reference/orm.html#search-domains) 
and uses a *polish notation* for logical operations (```AND```, ```OR``` and ```NOT```).

It could be quickly ugly to do a complex domain, but don't worry the builder makes all 
for you. :)

Each builder domain method creates an instance of ```Ang3\Component\Odoo\Expression\DomainInterface``` 
thats extends ```Ang3\Component\Odoo\Expression\ExpressionInterface```.
For each **search** methods, the client formats automatically the whole parameters array by calling 
the builder method ```criteriaParams()``` internally.

Here is a complete list of helper methods available in ```ExpressionBuilder```:

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
public function eq(string $fieldName, $value): Domain;

/**
 * Check if the field is NOT EQUAL TO the value.
 *
 * @param mixed $value
 */
public function neq(string $fieldName, $value): Domain;

/**
 * Check if the field is UNSET OR EQUAL TO the value.
 *
 * @param mixed $value
 */
public function ueq(string $fieldName, $value): Domain;

/**
 * Check if the field is LESS THAN the value.
 *
 * @param mixed $value
 */
public function lt(string $fieldName, $value): Domain;

/**
 * Check if the field is LESS THAN OR EQUAL the value.
 *
 * @param mixed $value
 */
public function lte(string $fieldName, $value): Domain;

/**
 * Check if the field is GREATER THAN the value.
 *
 * @param mixed $value
 */
public function gt(string $fieldName, $value): Domain;

/**
 * Check if the field is GREATER THAN OR EQUAL the value.
 *
 * @param mixed $value
 */
public function gte(string $fieldName, $value): Domain;

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
public function like(string $fieldName, $value, bool $strict = false, bool $caseSensitive = true): Domain;

/**
 * Check if the field is IS NOT LIKE the value.
 *
 * @param mixed $value
 */
public function notLike(string $fieldName, $value, bool $caseSensitive = true): Domain;

/**
 * Check if the field is IN values list.
 */
public function in(string $fieldName, array $values = []): Domain;

/**
 * Check if the field is NOT IN values list.
 */
public function notIn(string $fieldName, array $values = []): Domain;
```

Collection operations
---------------------

When you 

Links
-----

- **Back** to [Built-in methods](builtin_methods.md)
- **Back** to [Index](https://github.com/Ang3/php-odoo-api-client)