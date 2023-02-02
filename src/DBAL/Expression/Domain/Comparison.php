<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-api-client
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Expression\Domain;

/**
 * @author Joanis ROUANET <https://github.com/Ang3>
 */
class Comparison implements DomainInterface
{
    /**
     * Comparison operators.
     */
    public const UNSET_OR_EQUAL_TO = '=?';
    public const EQUAL_TO = '=';
    public const NOT_EQUAL_TO = '!=';
    public const LESS_THAN = '<';
    public const LESS_THAN_OR_EQUAL = '<=';
    public const GREATER_THAN = '>';
    public const GREATER_THAN_OR_EQUAL = '>=';
    public const EQUAL_LIKE = '=like';
    public const INSENSITIVE_EQUAL_LIKE = '=ilike';
    public const LIKE = 'like';
    public const NOT_LIKE = 'not like';
    public const INSENSITIVE_LIKE = 'ilike';
    public const INSENSITIVE_NOT_LIKE = 'not ilike';
    public const IN = 'in';
    public const NOT_IN = 'not in';

    /**
     * @var string[]
     */
    private static array $operators = [
        self::UNSET_OR_EQUAL_TO,
        self::EQUAL_TO,
        self::NOT_EQUAL_TO,
        self::LESS_THAN,
        self::LESS_THAN_OR_EQUAL,
        self::GREATER_THAN,
        self::GREATER_THAN_OR_EQUAL,
        self::EQUAL_LIKE,
        self::INSENSITIVE_EQUAL_LIKE,
        self::LIKE,
        self::NOT_LIKE,
        self::INSENSITIVE_LIKE,
        self::INSENSITIVE_NOT_LIKE,
        self::IN,
        self::NOT_IN,
    ];

    public function __construct(private string $fieldName, private string $operator, private mixed $value)
    {
    }

    public function __clone()
    {
        $this->value = \is_object($this->value) ? clone $this->value : $this->value;
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->toArray());
    }

    public function toArray(): array
    {
        return [$this->fieldName, $this->operator, $this->value];
    }

    /**
     * @return string[]
     */
    public static function getOperators(): array
    {
        return self::$operators;
    }

    public function getFieldName(): string
    {
        return $this->fieldName;
    }

    public function setFieldName(string $fieldName): self
    {
        $this->fieldName = $fieldName;

        return $this;
    }

    public function getOperator(): string
    {
        return $this->operator;
    }

    public function setOperator(string $operator): self
    {
        $this->operator = $operator;

        return $this;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function setValue(mixed $value): self
    {
        $this->value = $value;

        return $this;
    }
}
