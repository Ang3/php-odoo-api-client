<?php

namespace Ang3\Component\Odoo\Expression;

interface OperationInterface extends ExpressionInterface
{
    public function getType(): int;

    public function getId(): ?int;

    public function getData(): ?array;

    public function getCommand(): array;
}
