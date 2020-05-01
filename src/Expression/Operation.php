<?php

namespace Ang3\Component\Odoo\Expression;

class Operation implements OperationInterface
{
    /**
     * Odoo operations.
     */
    public const CREATE = 0;
    public const UPDATE = 1;
    public const DELETE = 2;
    public const REMOVE = 3;
    public const ADD = 4;
    public const CLEAR = 5;
    public const REPLACE = 6;

    /**
     * @var int
     */
    private $type;

    /**
     * @var int|null
     */
    private $id;

    /**
     * @var array|null
     */
    private $data;

    public function __construct(int $type, int $id = null, array $data = null)
    {
        $this->type = $type;
        $this->id = $id;
        $this->data = $data;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public function setData(?array $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function toArray(): array
    {
        return $this->getCommand();
    }

    public function getCommand(): array
    {
        if (null !== $this->data) {
            foreach ($this->data as $key => $value) {
                if ($value instanceof OperationInterface) {
                    $this->data[$key] = $value->getCommand();
                }
            }
        }

        return [$this->type, $this->id ?: 0, is_array($this->data) ? $this->data : 0];
    }
}
