<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-api-client
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\Exception;

/**
 * @author Joanis ROUANET <https://github.com/Ang3>
 */
class AuthenticationException extends RequestException
{
    public function __construct(\Throwable $previous = null)
    {
        parent::__construct('Bad credentials', 0, $previous);
    }
}
