<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-api-client
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Query;

use Ang3\Component\Odoo\DBAL\RecordManager;

abstract class AbstractQuery implements QueryInterface
{
    protected array $parameters = [];
    protected array $options = [];

    public function __construct(
        protected readonly RecordManager $recordManager,
        protected string $name,
        protected string $method
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function setMethod(string $method): static
    {
        $this->method = $method;

        return $this;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function setParameters(array $parameters = []): static
    {
        $this->parameters = $parameters;

        return $this;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOptions(array $options = []): static
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Add an option on the query.
     */
    public function addOption(string $name, mixed $value): static
    {
        $this->options[$name] = $value;

        return $this;
    }

    /**
     * Execute the query.
     * Allowed methods: all.
     */
    public function execute(): mixed
    {
        return $this->recordManager->executeQuery($this);
    }

    /**
     * Gets the related manager of the query.
     */
    public function getRecordManager(): RecordManager
    {
        return $this->recordManager;
    }
}
