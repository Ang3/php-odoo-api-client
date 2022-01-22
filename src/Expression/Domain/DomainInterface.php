<?php

namespace Ang3\Component\Odoo\Expression\Domain;

use IteratorAggregate;

interface DomainInterface extends IteratorAggregate
{
    public function toArray(): array;
}
