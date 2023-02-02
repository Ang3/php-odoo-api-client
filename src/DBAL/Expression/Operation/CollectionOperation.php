<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-api-client
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Expression\Operation;

/**
 * @author Joanis ROUANET <https://github.com/Ang3>
 */
class CollectionOperation implements OperationInterface
{
    /**
     * Operations key.
     */
    public const CREATE = 0;
    public const UPDATE = 1;
    public const DELETE = 2;
    public const REMOVE = 3;
    public const ADD = 4;
    public const CLEAR = 5;
    public const REPLACE = 6;

    public function __construct(private int $type, private int $id = 0, private int|array $data = 0)
    {
    }

    /**
     * @throws \InvalidArgumentException when data is empty
     */
    public static function create(array $data): self
    {
        if (!$data) {
            throw new \InvalidArgumentException('Data cannot be empty');
        }

        return new self(self::CREATE, 0, $data);
    }

    /**
     * @throws \InvalidArgumentException when data is empty
     */
    public static function update(int $id, array $data = []): self
    {
        if (!$data) {
            throw new \InvalidArgumentException('Data cannot be empty');
        }

        return new self(self::UPDATE, $id, $data);
    }

    public static function add(int $id): self
    {
        return new self(self::ADD, $id);
    }

    public static function remove(int $id): self
    {
        return new self(self::REMOVE, $id);
    }

    public static function delete(int $id): self
    {
        return new self(self::DELETE, $id);
    }

    /**
     * @param int[] $ids
     */
    public static function replace(array $ids): self
    {
        return new self(self::REPLACE, 0, $ids);
    }

    public static function clear(): self
    {
        return new self(self::CLEAR);
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

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getData(): int|array
    {
        return $this->data;
    }

    public function setData(int|array $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function toArray(): array
    {
        return [$this->type, $this->id, $this->data];
    }
}
