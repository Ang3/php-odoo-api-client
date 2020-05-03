<?php

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
        return is_object($value) ? sprintf('instance of %s', get_class($value)) : gettype($value);
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
