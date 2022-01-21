<?php

namespace Ang3\Component\Odoo\DBAL\Expression;

use ArrayIterator;

class CustomDomain implements DomainInterface
{
    /**
     * @var array
     */
    private $data;

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->toArray());
    }

    public function toArray(): array
    {
        return $this->data;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
    }
}
