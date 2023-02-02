<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-api-client
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Schema;

class SchemaException extends \RuntimeException
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
