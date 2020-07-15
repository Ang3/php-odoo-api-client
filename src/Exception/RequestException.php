<?php

namespace Ang3\Component\Odoo\Exception;

class RequestException extends \RuntimeException implements ExceptionInterface
{
    public function __construct(string $message, int $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
