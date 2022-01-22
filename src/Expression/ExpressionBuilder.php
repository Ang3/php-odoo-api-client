<?php

namespace Ang3\Component\Odoo\Expression;

use Ang3\Component\Odoo\Expression\Domain\Comparison;
use Ang3\Component\Odoo\Expression\Domain\CompositeDomain;
use Ang3\Component\Odoo\Expression\Domain\DomainInterface;
use Ang3\Component\Odoo\Expression\Exception\ConversionException;
use Ang3\Component\Odoo\Expression\Operation\CollectionOperation;
use DateTime;
use DateTimeInterface;
use Exception;
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
    public function createRecord(array $data): CollectionOperation
    {
        return CollectionOperation::create($data);
    }

    /**
     * Updates an existing record of id $id with data.
     * /!\ Can not be used in record create operation.
     *
     * @throws InvalidArgumentException when $data is empty
     */
    public function updateRecord(int $id, array $data): CollectionOperation
    {
        if (!$data) {
            throw new InvalidArgumentException('Data cannot be empty');
        }

        return CollectionOperation::update($id, $data);
    }

    /**
     * Adds an existing record of id $id to the collection.
     */
    public function addRecord(int $id): CollectionOperation
    {
        return CollectionOperation::add($id);
    }

    /**
     * Removes the record of id $id from the collection, but does not delete it.
     * /!\ Can not be used in record create operation.
     */
    public function removeRecord(int $id): CollectionOperation
    {
        return CollectionOperation::remove($id);
    }

    /**
     * Removes the record of id $id from the collection, then deletes it from the database.
     * /!\ Can not be used in record create operation.
     */
    public function deleteRecord(int $id): CollectionOperation
    {
        return CollectionOperation::delete($id);
    }

    /**
     * Replaces all existing records in the collection by the $ids list,
     * Equivalent to using the command "clear" followed by a command "add" for each id in $ids.
     */
    public function replaceRecords(array $ids = []): CollectionOperation
    {
        return CollectionOperation::replace($ids);
    }

    /**
     * Removes all records from the collection, equivalent to using the command "remove" on every record explicitly.
     * /!\ Can not be used in record create operation.
     */
    public function clearRecords(): CollectionOperation
    {
        return CollectionOperation::clear();
    }

    /**
     * @throws InvalidArgumentException when $criteria value is not valid
     * @throws ConversionException      on data conversion failure
     */
    public function normalizeDomains(iterable $criteria = null): array
    {
        if (!$criteria) {
            return [];
        }

        if (is_array($criteria)) {
            $normalizedCriteria = $this->andX();

            foreach ($criteria as $fieldName => $value) {
                $comparison = $this->eq($fieldName, $this->formatValue($value));

                if (1 === count($criteria)) {
                    $normalizedCriteria = $comparison;
                    break;
                }

                $normalizedCriteria->add($comparison);
            }

            $criteria = $normalizedCriteria;
        }

        if (!$criteria instanceof DomainInterface) {
            throw new InvalidArgumentException(sprintf('Expected parameter #1 of type %s|array<%s|array>, %s given', DomainInterface::class, DomainInterface::class, gettype($criteria)));
        }

        $criteriaArray = $this->formatValue($criteria->toArray());

        if (!$criteriaArray) {
            return $this->normalizeDomains();
        }

        if ($criteria instanceof CompositeDomain) {
            dump($criteriaArray);

            return $criteriaArray;
        }

        dump([$criteriaArray]);

        return [$criteriaArray];
    }

    /**
     * @throws ConversionException on data conversion failure
     */
    public function normalizeData(array $data = []): array
    {
        return $this->formatValue($data);
    }

    /**
     * @param mixed $value
     *
     * @throws ConversionException on data conversion failure
     *
     * @return mixed
     */
    private function formatValue($value)
    {
        if (is_scalar($value)) {
            return $value;
        }

        if (is_array($value) || is_iterable($value)) {
            $values = [];

            foreach ($value as $key => $aValue) {
                $values[$key] = $this->formatValue($aValue);
            }

            return $values;
        }

        if (is_object($value)) {
            if ($value instanceof DomainInterface) {
                return $this->formatValue($value->toArray());
            }

            if ($value instanceof CollectionOperation) {
                return $this->formatValue($value->toArray());
            }

            if ($value instanceof DateTimeInterface) {
                try {
                    $date = new DateTime(sprintf('@%s', $value->getTimestamp()));
                } catch (Exception $e) {
                    throw new ConversionException(sprintf('Failed to convert date from timestamp "%d"', $value->getTimestamp()), 0, $e);
                }

                $date->setTimezone(new \DateTimeZone('UTC'));

                return $date->format('Y-m-d H:i:s');
            }
        }

        try {
            return (string) $value;
        } catch (Exception $e) {
            throw new ConversionException(sprintf('Failed to convert value of type "%s" to string.', gettype($value)), 0, $e);
        }
    }
}
