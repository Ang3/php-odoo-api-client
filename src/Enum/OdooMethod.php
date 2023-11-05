<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-api-client
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\Enum;

enum OdooMethod: string
{
    case Login = 'login';
    case ExecuteKw = 'execute_kw';
    case Version = 'version';
}
