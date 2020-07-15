<?php

namespace Ang3\Component\Odoo\Exception;

use Ang3\Component\XmlRpc\Exception\RequestException;

class AuthenticationException extends RequestException
{
    public function __construct(\Throwable $previous = null)
    {
        parent::__construct('Bad credentials', 0, $previous);
    }
}
