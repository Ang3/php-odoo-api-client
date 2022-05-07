<?php

namespace Ang3\Component\Odoo\Expression\Domain;

use IteratorAggregate;

/**
 * @author Joanis ROUANET <https://github.com/Ang3>
 */
interface DomainInterface extends IteratorAggregate
{
    public function toArray(): array;
}
