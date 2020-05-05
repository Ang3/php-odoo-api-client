<?php

namespace Ang3\Component\Odoo\Expression;

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
    private static $operators = [
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

    /**
     * @var string
     */
    private $fieldName;

    /**
     * @var string
     */
    private $operator;

    /**
     * @var mixed
     */
    private $value;

    /**
     * @param mixed $value
     */
    public function __construct(string $fieldName, string $operator, $value)
    {
        $this->fieldName = $fieldName;
        $this->operator = $operator;
        $this->value = $value;
    }

    public function __clone()
    {
        $this->value = is_object($this->value) ? clone $this->value : $this->value;
    }

    public function __toString(): string
    {
        return sprintf('%s %s %s', $this->fieldName, $this->operator, $this->toString($this->value));
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

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value): self
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @internal
     *
     * @param mixed $value
     */
    private function toString($value = null): string
    {
        if (null === $value) {
            return 'NULL';
        }

        if (is_bool($value)) {
            $value = $value ? 'true' : 'false';
        } elseif (is_int($value) || is_float($value)) {
            $value = (string) $value;
        } elseif (is_string($value)) {
            $value = sprintf('"%s"', addslashes($value));
        } elseif (is_array($value)) {
            $values = $value;

            foreach ($values as $key => $subValue) {
                $values[$key] = $this->toString($subValue);
            }

            return sprintf('[%s]', implode(', ', $values));
        } else {
            $value = sprintf('"%s"', json_encode($value, JSON_HEX_QUOT) ?: '');
        }

        return $value;
    }
}
