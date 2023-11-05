<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-api-client
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Schema\Enum;

enum DateTimeFormat: string
{
    case Short = 'Y-m-d';
    case Long = 'Y-m-d H:i:s';
}
