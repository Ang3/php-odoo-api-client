<?php

namespace Ang3\Component\Odoo\Exception;

class AuthenticationException extends RequestException
{
    public function __construct(\Throwable $previous = null)
    {
        parent::__construct('Bad credentials', 0, $previous);
    }
}
