<?php

namespace Ang3\Component\Odoo\Exception;

class AuthenticationException extends RequestException
{
    public const DEFAULT_MESSAGE = 'Bad credentials';

    public function __construct(string $message = null, int $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message ?: self::DEFAULT_MESSAGE, $code, $previous);
    }
}
