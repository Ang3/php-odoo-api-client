<?php

namespace Ang3\Component\Odoo\DBAL\Repository;

class RecordNotFoundException extends \RuntimeException
{
    /**
     * @var string
     */
    private $modelName;

    /**
     * @var int
     */
    private $id;

    public function __construct(string $modelName, int $id)
    {
        $this->modelName = $modelName;
        $this->id = $id;

        parent::__construct(sprintf('No record found for model "%s" with ID #%d.', $modelName, $id));
    }

    public function getModelName(): string
    {
        return $this->modelName;
    }

    public function getId(): int
    {
        return $this->id;
    }
}
