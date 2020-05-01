<?php

namespace Ang3\Component\Odoo\Expression;

class CustomDomain implements DomainInterface
{
    /**
     * @var array
     */
    private $expr;

    public function __construct(array $expr)
    {
        $this->expr = $expr;
    }

    public function toArray(): array
    {
        return $this->expr;
    }

    public function getExpr(): array
    {
        return $this->expr;
    }

    public function setExpr(array $expr): self
    {
        $this->expr = $expr;

        return $this;
    }
}
