<?php

namespace Ang3\Component\Odoo\Expression;

use InvalidArgumentException;

class ExpressionBuilder
{
    /**
     * Odoo operations key.
     */
    public const CREATE = 0;
    public const UPDATE = 1;
    public const DELETE = 2;
    public const REMOVE = 3;
    public const ADD = 4;
    public const CLEAR = 5;
    public const REPLACE = 6;

    /**
     * Create a logical operation "AND".
     */
    public function andX(DomainInterface ...$domains): CompositeDomain
    {
        return new CompositeDomain(CompositeDomain::AND, $domains ?: []);
    }

    /**
     * Create a logical operation "OR".
     */
    public function orX(DomainInterface ...$domains): CompositeDomain
    {
        return new CompositeDomain(CompositeDomain::OR, $domains ?: []);
    }

    /**
     * Create a logical operation "NOT".
     */
    public function notX(DomainInterface ...$domains): CompositeDomain
    {
        return new CompositeDomain(CompositeDomain::NOT, $domains ?: []);
    }

    /**
     * Check if the field is EQUAL TO the value.
     *
     * @param mixed $value
     */
    public function eq(string $fieldName, $value): Comparison
    {
        return new Comparison($fieldName, Comparison::EQUAL_TO, $value);
    }

    /**
     * Check if the field is NOT EQUAL TO the value.
     *
     * @param mixed $value
     */
    public function neq(string $fieldName, $value): Comparison
    {
        return new Comparison($fieldName, Comparison::NOT_EQUAL_TO, $value);
    }

    /**
     * Check if the field is UNSET OR EQUAL TO the value.
     *
     * @param mixed $value
     */
    public function ueq(string $fieldName, $value): Comparison
    {
        return new Comparison($fieldName, Comparison::UNSET_OR_EQUAL_TO, $value);
    }

    /**
     * Check if the field is LESS THAN the value.
     *
     * @param mixed $value
     */
    public function lt(string $fieldName, $value): Comparison
    {
        return new Comparison($fieldName, Comparison::LESS_THAN, $value);
    }

    /**
     * Check if the field is LESS THAN OR EQUAL the value.
     *
     * @param mixed $value
     */
    public function lte(string $fieldName, $value): Comparison
    {
        return new Comparison($fieldName, Comparison::LESS_THAN_OR_EQUAL, $value);
    }

    /**
     * Check if the field is GREATER THAN the value.
     *
     * @param mixed $value
     */
    public function gt(string $fieldName, $value): Comparison
    {
        return new Comparison($fieldName, Comparison::GREATER_THAN, $value);
    }

    /**
     * Check if the field is GREATER THAN OR EQUAL the value.
     *
     * @param mixed $value
     */
    public function gte(string $fieldName, $value): Comparison
    {
        return new Comparison($fieldName, Comparison::GREATER_THAN_OR_EQUAL, $value);
    }

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
    public function like(string $fieldName, $value, bool $strict = false, bool $caseSensitive = true): Comparison
    {
        if ($strict) {
            $operator = $caseSensitive ? Comparison::EQUAL_LIKE : Comparison::INSENSITIVE_EQUAL_LIKE;
        } else {
            $operator = $caseSensitive ? Comparison::LIKE : Comparison::INSENSITIVE_LIKE;
        }

        return new Comparison($fieldName, $operator, $value);
    }

    /**
     * Check if the field is IS NOT LIKE the value.
     *
     * @param mixed $value
     */
    public function notLike(string $fieldName, $value, bool $caseSensitive = true): Comparison
    {
        $operator = $caseSensitive ? Comparison::NOT_LIKE : Comparison::INSENSITIVE_NOT_LIKE;

        return new Comparison($fieldName, $operator, $value);
    }

    /**
     * Check if the field is IN values list.
     *
     * @param bool|int|float|string|array $values
     */
    public function in(string $fieldName, $values): Comparison
    {
        return new Comparison($fieldName, Comparison::IN, $this->getValues($values));
    }

    /**
     * Check if the field is NOT IN values list.
     *
     * @param bool|int|float|string|array $values
     */
    public function notIn(string $fieldName, $values): Comparison
    {
        return new Comparison($fieldName, Comparison::NOT_IN, $this->getValues($values));
    }

    /**
     * @internal
     *
     * @param bool|int|float|string|array $values
     */
    private function getValues($values): array
    {
        return is_array($values) ? $values : [$values];
    }

    /**
     * Adds a new record created from data.
     *
     * @throws InvalidArgumentException when $data is empty
     */
    public function createRecord(array $data): array
    {
        if (!$data) {
            throw new InvalidArgumentException('Data cannot be empty');
        }

        return [self::CREATE, 0, $data];
    }

    /**
     * Updates an existing record of id $id with data.
     * /!\ Can not be used in record create operation.
     *
     * @throws InvalidArgumentException when $data is empty
     */
    public function updateRecord(int $id, array $data): array
    {
        if (!$data) {
            throw new InvalidArgumentException('Data cannot be empty');
        }

        return [self::UPDATE, $id, $data];
    }

    /**
     * Adds an existing record of id $id to the collection.
     */
    public function addRecord(int $id): array
    {
        return [self::ADD, $id, 0];
    }

    /**
     * Removes the record of id $id from the collection, but does not delete it.
     * /!\ Can not be used in record create operation.
     */
    public function removeRecord(int $id): array
    {
        return [self::REMOVE, $id, 0];
    }

    /**
     * Removes the record of id $id from the collection, then deletes it from the database.
     * /!\ Can not be used in record create operation.
     */
    public function deleteRecord(int $id): array
    {
        return [self::DELETE, $id, 0];
    }

    /**
     * Replaces all existing records in the collection by the $ids list,
     * Equivalent to using the command "clear" followed by a command "add" for each id in $ids.
     */
    public function replaceRecords(array $ids = []): array
    {
        return [self::REPLACE, 0, $ids];
    }

    /**
     * Removes all records from the collection, equivalent to using the command "remove" on every record explicitly.
     * /!\ Can not be used in record create operation.
     */
    public function clearRecords(): array
    {
        return [self::CLEAR, 0, 0];
    }

    /**
     * @param DomainInterface|array|null $criteria
     *
     * @throws InvalidArgumentException when $criteria value is not valid
     */
    public function normalizeDomains($criteria = null): array
    {
        if ($criteria instanceof DomainInterface) {
            return $criteria instanceof CompositeDomain ? [$criteria->toArray()] : [[$criteria->toArray()]];
        }

        if (!is_array($criteria)) {
            throw new InvalidArgumentException(sprintf('Expected $criteria value of type %s|array<%s|array>, %s given', DomainInterface::class, DomainInterface::class, gettype($criteria)));
        }

        return $this->formatDomains($criteria);
    }

    /**
     * @internal
     */
    private function formatDomains(array $data = []): array
    {
        foreach ($data as $key => $value) {
            if ($value instanceof DomainInterface) {
                $data[$key] = $value->toArray();
                continue;
            }

            if (is_array($value)) {
                $data[$key] = $this->formatDomains($value);
            }
        }

        return $data;
    }
}
