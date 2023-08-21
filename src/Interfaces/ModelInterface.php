<?php

namespace Ang3\Component\Odoo\Interfaces;

use Ang3\Component\Odoo\DBAL\Expression\DomainInterface;
use Illuminate\Support\Collection;

interface ModelInterface
{
    public function __get($key);

    public function __set(string $key, mixed $value);

    public function get(): Collection;

    public static function where(DomainInterface $condition): self;

    public function find(int $id): mixed;

    public function findOrFail(int $id): self;

    public function findBy(string|int $field, $value): self;

    public function save(): self;

    public function delete(): bool;

    public static function create(array $attributes): self;

    public function paginate(int $per_page = 100): array;
}
