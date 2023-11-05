<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-api-client
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Schema;

use Ang3\Component\Odoo\DBAL\Schema\Enum\DateTimeFormat;
use Ang3\Component\Odoo\DBAL\Schema\Enum\FieldType;

class Field
{
    private Model $model;
    private int $id;
    private string $name;
    private FieldType $type;
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
        $this->type = FieldType::from((string) $data['ttype']);
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

    public function getType(): FieldType
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
        return FieldType::Binary === $this->type;
    }

    public function isBoolean(): bool
    {
        return FieldType::Boolean === $this->type;
    }

    public function isInteger(): bool
    {
        return FieldType::Integer === $this->type;
    }

    public function isFloat(): bool
    {
        return \in_array($this->type, [FieldType::Float, FieldType::Monetary], true);
    }

    public function isNumber(): bool
    {
        return $this->isInteger() || $this->isFloat();
    }

    public function isString(): bool
    {
        return \in_array($this->type, [FieldType::Char, FieldType::Text, FieldType::Html], true);
    }

    public function isDate(): bool
    {
        return \in_array($this->type, [FieldType::Date, FieldType::DateTime], true);
    }

    public function getDateFormat(): DateTimeFormat
    {
        return FieldType::DateTime === $this->type ? DateTimeFormat::Long : DateTimeFormat::Short;
    }

    public function isSelection(): bool
    {
        return FieldType::Selection === $this->type;
    }

    public function isSelectable(): bool
    {
        return null !== $this->selection;
    }

    public function isAssociation(): bool
    {
        return $this->isSingleAssociation() || $this->isMultipleAssociation();
    }

    public function isSingleAssociation(): bool
    {
        return FieldType::ManyToOne === $this->type;
    }

    public function isMultipleAssociation(): bool
    {
        return \in_array($this->type, [
            FieldType::ManyToMany,
            FieldType::OneToMany,
        ], true);
    }
}
