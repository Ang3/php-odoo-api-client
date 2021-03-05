<?php

namespace Ang3\Component\Odoo\DBAL\Expression;

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
