<?php

namespace Ang3\Component\Odoo\Expression;

interface DomainInterface
{
    public function __toString(): string;

    public function toArray(): array;
}
