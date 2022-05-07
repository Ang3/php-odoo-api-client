<?php

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
