<?php

namespace Ang3\Component\Odoo\Query\Domain;

class DomainBuilder
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
     * Unset or equals to.
     *
     * @param mixed $value
     */
    public function ueq(string $fieldName, $value): Domain
    {
        return $this->createDomain($fieldName, Domain::UNSET_OR_EQUAL_TO, $value);
    }

    /**
     * @param mixed $value
     */
    public function eq(string $fieldName, $value): Domain
    {
        return $this->createDomain($fieldName, Domain::EQUAL_TO, $value);
    }

    /**
     * @param mixed $value
     */
    public function neq(string $fieldName, $value): Domain
    {
        return $this->createDomain($fieldName, Domain::NOT_EQUAL_TO, $value);
    }

    /**
     * @param mixed $value
     */
    public function lt(string $fieldName, $value): Domain
    {
        return $this->createDomain($fieldName, Domain::LESS_THAN, $value);
    }

    /**
     * @param mixed $value
     */
    public function lte(string $fieldName, $value): Domain
    {
        return $this->createDomain($fieldName, Domain::LESS_THAN_OR_EQUAL, $value);
    }

    /**
     * @param mixed $value
     */
    public function gt(string $fieldName, $value): Domain
    {
        return $this->createDomain($fieldName, Domain::GREATER_THAN, $value);
    }

    /**
     * @param mixed $value
     */
    public function gte(string $fieldName, $value): Domain
    {
        return $this->createDomain($fieldName, Domain::GREATER_THAN_OR_EQUAL, $value);
    }

    /**
     * @param mixed $value
     */
    public function like(string $fieldName, $value, bool $strict = false, bool $caseSensitive = true): Domain
    {
        if ($strict) {
            $operator = $caseSensitive ? Domain::EQUAL_LIKE : Domain::INSENSITIVE_EQUAL_LIKE;
        } else {
            $operator = $caseSensitive ? Domain::LIKE : Domain::INSENSITIVE_LIKE;
        }

        return $this->createDomain($fieldName, $operator, $value);
    }

    /**
     * @param mixed $value
     */
    public function notLike(string $fieldName, $value, bool $caseSensitive = true): Domain
    {
        $operator = $caseSensitive ? Domain::NOT_LIKE : Domain::INSENSITIVE_NOT_LIKE;

        return $this->createDomain($fieldName, $operator, $value);
    }

    public function in(string $fieldName, array $values = []): Domain
    {
        return $this->createDomain($fieldName, Domain::IN, $values);
    }

    public function notIn(string $fieldName, array $values = []): Domain
    {
        return $this->createDomain($fieldName, Domain::NOT_IN, $values);
    }

    /**
     * @internal
     *
     * @param mixed $value
     */
    private function createDomain(string $fieldName, string $operator, $value): Domain
    {
        return new Domain($fieldName, $operator, $value);
    }
}
