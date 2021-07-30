<?php

namespace Ang3\Component\Odoo\DBAL\Query;

use Ang3\Component\Odoo\DBAL\RecordManager;

abstract class AbstractQuery implements QueryInterface
{
    /**
     * @var RecordManager
     */
    protected $recordManager;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $method;

    /**
     * @var array
     */
    protected $parameters = [];

    /**
     * @var array
     */
    protected $options = [];

    public function __construct(RecordManager $recordManager, string $name, string $method)
    {
        $this->recordManager = $recordManager;
        $this->name = $name;
        $this->method = $method;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return $this
     */
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return $this
     */
    public function setMethod(string $method)
    {
        $this->method = $method;

        return $this;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @return $this
     */
    public function setParameters(array $parameters = [])
    {
        $this->parameters = $parameters;

        return $this;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @return $this
     */
    public function setOptions(array $options = [])
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Add an option on the query.
     *
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    public function addOption(string $name, mixed $value)
    {
        $this->options[$name] = $value;

        return $this;
    }

    /**
     * Execute the query.
     * Allowed methods: all.
     *
     * @return mixed
     */
    public function execute()
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
