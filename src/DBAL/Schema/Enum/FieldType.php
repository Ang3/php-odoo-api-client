<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-api-client
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Schema\Enum;

enum FieldType: string
{
    // Basics
    case Binary = 'binary';
    case Boolean = 'boolean';
    case Char = 'char';
    case Date = 'date';
    case DateTime = 'datetime';
    case Float = 'float';
    case Html = 'html';
    case Integer = 'integer';
    case Monetary = 'monetary';
    case Selection = 'selection';
    case Text = 'text';

    // Relationships
    case ManyToOne = 'many2one';
    case ManyToMany = 'many2many';
    case OneToMany = 'one2many';
}
