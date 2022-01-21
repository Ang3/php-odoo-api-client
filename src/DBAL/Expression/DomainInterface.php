<?php

namespace Ang3\Component\Odoo\DBAL\Expression;

use IteratorAggregate;

interface DomainInterface extends IteratorAggregate
{
    public function toArray(): array;
}
