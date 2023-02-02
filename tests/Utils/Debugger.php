<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-api-client
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\Tests\Utils;

class Debugger
{
    /**
     * Get the type of a value as string for debugging.
     *
     * @param mixed $value
     */
    public function debugType($value): string
    {
        return \is_object($value) ? sprintf('instance of %s', $value::class) : \gettype($value);
    }

    /**
     * Get the type of a value as string for debugging.
     *
     * @param mixed $value
     */
    public function debugBool($value): string
    {
        return (bool) $value ? 'TRUE' : 'FALSE';
    }
}
