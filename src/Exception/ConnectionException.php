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
class ConnectionException extends \InvalidArgumentException implements ExceptionInterface
{
    public static function invalidDsn(string $dsn, ?string $message = null, ?\Throwable $previous = null): self
    {
        return new self(sprintf('The DSN "%s" is not valid - %s', $dsn, $message ?: 'Unknown error'), 0, $previous);
    }
}
