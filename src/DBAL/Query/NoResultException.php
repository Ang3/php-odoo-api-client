<?php

namespace Ang3\Component\Odoo\DBAL\Query;

class NoResultException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct('The query returned no result.');
    }
}
