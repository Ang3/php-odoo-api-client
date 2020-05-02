<?php

namespace Ang3\Component\Odoo\Expression;

use InvalidArgumentException;

class ExpressionBuilder
{
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
     */
    public function in(string $fieldName, array $values = []): Comparison
    {
        return new Comparison($fieldName, Comparison::IN, $values);
    }

    /**
     * Check if the field is NOT IN values list.
     */
    public function notIn(string $fieldName, array $values = []): Comparison
    {
        return new Comparison($fieldName, Comparison::NOT_IN, $values);
    }

    /**
     * Adds a new record created from data.
     */
    public function createRecord(array $data): Operation
    {
        return new Operation(Operation::CREATE, null, $data);
    }

    /**
     * Updates an existing record of id $id with data.
     * /!\ Can not be used in record insert query.
     */
    public function updateRecord(int $id, array $data): Operation
    {
        return new Operation(Operation::UPDATE, $id, $data);
    }

    /**
     * Adds an existing record of id $id to the collection.
     */
    public function addRecord(int $id): Operation
    {
        return new Operation(Operation::ADD, $id);
    }

    /**
     * Removes the record of id $id from the collection, but does not delete it.
     * /!\ Can not be used in record insert query.
     */
    public function removeRecord(int $id): Operation
    {
        return new Operation(Operation::REMOVE, $id);
    }

    /**
     * Removes the record of id $id from the collection, then deletes it from the database.
     * /!\ Can not be used in record insert query.
     */
    public function deleteRecord(int $id): Operation
    {
        return new Operation(Operation::DELETE, $id);
    }

    /**
     * Replaces all existing records in the collection by the $ids list,
     * Equivalent to using the command "clear" followed by a command "add" for each id in $ids.
     */
    public function replaceRecords(array $ids = []): Operation
    {
        return new Operation(Operation::REPLACE, null, $ids);
    }

    /**
     * Removes all records from the collection, equivalent to using the command "remove" on every record explicitly.
     * /!\ Can not be used in record insert query.
     */
    public function clearRecords(): Operation
    {
        return new Operation(Operation::CLEAR);
    }

    /**
     * @param DomainInterface|array|null $criteria
     *
     * @throws InvalidArgumentException when $criteria is not valid
     */
    public function criteriaParams($criteria = null): array
    {
        if (!$criteria) {
            return [];
        }

        if ($criteria instanceof DomainInterface) {
            if ($criteria instanceof CompositeDomain) {
                $criteria = $criteria->normalize();
            }

            if ($criteria instanceof CompositeDomain || $criteria instanceof CustomDomain) {
                return [$criteria->toArray()];
            }

            return [[$criteria->toArray()]];
        }

        $criteria = array_values((array) $criteria);
        $andX = $this->andX();

        foreach ($criteria as $key => $domain) {
            if (!($domain instanceof DomainInterface)) {
                if (!is_array($domain)) {
                    $invalidType = is_object($domain) ? get_class($domain) : gettype($domain);
                    throw new InvalidArgumentException(sprintf('Expected criteria of type %s|array, %s given', DomainInterface::class, $invalidType));
                }

                $domain = new CustomDomain($domain);
            }

            $andX->add($domain);
        }

        return $this->criteriaParams($andX);
    }

    /**
     * @throws InvalidArgumentException when data is empty
     */
    public function dataParams(array $data): array
    {
        if (!$data) {
            throw new InvalidArgumentException('Data parameters cannot be empty');
        }

        foreach ($data as $key => $value) {
            if ($value instanceof OperationInterface) {
                $data[$key] = $value->getCommand();
                continue;
            }

            if (is_array($value)) {
                $data[$key] = $this->dataParams($value);
            }
        }

        return $data;
    }
}
