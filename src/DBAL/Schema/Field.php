<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-api-client
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Schema;

class Field
{
    /**
     * List of Odoo field types constants.
     */
    public const T_BINARY = 'binary';
    public const T_BOOLEAN = 'boolean';
    public const T_CHAR = 'char';
    public const T_DATE = 'date';
    public const T_DATETIME = 'datetime';
    public const T_FLOAT = 'float';
    public const T_HTML = 'html';
    public const T_INTEGER = 'integer';
    public const T_MONETARY = 'monetary';
    public const T_SELECTION = 'selection';
    public const T_TEXT = 'text';
    public const T_MANY_TO_ONE = 'many2one';
    public const T_MANY_TO_MANY = 'many2many';
    public const T_ONE_TO_MANY = 'one2many';

    /**
     * Date formats.
     */
    public const DATE_FORMAT = 'Y-m-d';
    public const DATETIME_FORMAT = 'Y-m-d H:i:s';

    private Model $model;
    private int $id;
    private string $name;
    private string $type;
    private bool $required;
    private bool $readOnly;
    private ?string $displayName;
    private ?int $size;
    private ?Selection $selection;
    private ?string $targetModelName;
    private ?string $targetFieldName;

    public function __construct(array $data)
    {
        $this->id = (int) $data['id'];
        $this->name = (string) $data['name'];
        $this->type = (string) $data['ttype'];
        $this->required = (bool) $data['required'];
        $this->readOnly = (bool) $data['readonly'];
        $this->displayName = $data['display_name'] ?? null;
        $this->size = $data['size'] ?? null;
        $this->selection = $data['selection'] ?? null;
        $this->targetModelName = $data['relation'] ?? null;
        $this->targetFieldName = $data['relation_field'] ?? null;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    public function setModel(Model $model): self
    {
        $this->model = $model;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function isReadOnly(): bool
    {
        return $this->readOnly;
    }

    public function getDisplayName(): string
    {
        return $this->displayName ?: $this->name;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function getSelection(): ?Selection
    {
        return $this->selection;
    }

    public function getTargetModelName(): ?string
    {
        return $this->targetModelName;
    }

    public function getTargetFieldName(): ?string
    {
        return $this->targetFieldName;
    }

    public function isIdentifier(): bool
    {
        return 'id' === $this->name;
    }

    public function isBinary(): bool
    {
        return self::T_BINARY === $this->type;
    }

    public function isBoolean(): bool
    {
        return self::T_BOOLEAN === $this->type;
    }

    public function isInteger(): bool
    {
        return self::T_INTEGER === $this->type;
    }

    public function isFloat(): bool
    {
        return \in_array($this->type, [self::T_FLOAT, self::T_MONETARY], true);
    }

    public function isNumber(): bool
    {
        return $this->isInteger() || $this->isFloat();
    }

    public function isString(): bool
    {
        return \in_array($this->type, [self::T_CHAR, self::T_TEXT, self::T_HTML], true);
    }

    public function isDate(): bool
    {
        return \in_array($this->type, [self::T_DATE, self::T_DATETIME], true);
    }

    public function getDateFormat(): string
    {
        return self::T_DATETIME === $this->type ? self::DATETIME_FORMAT : self::DATE_FORMAT;
    }

    public function isSelection(): bool
    {
        return self::T_SELECTION === $this->type;
    }

    public function isSelectable(): bool
    {
        return null !== $this->selection;
    }

    public function isAssociation(): bool
    {
        return \in_array($this->type, [
            self::T_MANY_TO_ONE,
            self::T_MANY_TO_MANY,
            self::T_ONE_TO_MANY,
        ], true);
    }

    public function isSingleAssociation(): bool
    {
        return self::T_MANY_TO_ONE === $this->type;
    }

    public function isMultipleAssociation(): bool
    {
        return \in_array($this->type, [
            self::T_MANY_TO_MANY,
            self::T_ONE_TO_MANY,
        ], true);
    }
}
