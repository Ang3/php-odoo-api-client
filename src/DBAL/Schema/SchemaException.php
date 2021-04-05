<?php

namespace Ang3\Component\Odoo\DBAL\Schema;

use RuntimeException;

class SchemaException extends RuntimeException
{
    public static function fieldNotFound(string $fieldName, Model $model): self
    {
        return new self(sprintf('The field "%s" does not exist in model "%s"', $fieldName, $model->getName()));
    }

    public static function modelNotFound(string $modelName): self
    {
        return new self(sprintf('The model "%s" was not found on the database', $modelName));
    }
}
