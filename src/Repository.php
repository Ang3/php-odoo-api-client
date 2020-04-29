<?php

namespace Ang3\Component\Odoo;

class Repository
{
    /**
     * Default ORM methods.
     */
    public const CREATE = 'create';
    public const READ = 'read';
    public const WRITE = 'write';
    public const DELETE = 'unlink';
    public const SEARCH = 'search';
    public const SEARCH_COUNT = 'search_count';
    public const SEARCH_READ = 'search_read';
    public const LIST_FIELDS = 'fields_get';

    /**
     * @var Manager
     */
    private $manager;

    /**
     * @var string
     */
    private $modelName;

    public function __construct(Manager $manager, string $modelName)
    {
        $this->manager = $manager;
        $this->modelName = $modelName;
    }

    /**
     * Create a record.
     */
    public function insert(array $data = []): int
    {
        return $this->manager
            ->getClient()
            ->call($this->modelName, self::CREATE, [$data]);
    }

    /**
     * Search record(s).
     */
    public function search(array $parameters = [], array $options = []): array
    {
        return (array) $this->manager
            ->getClient()
            ->call($this->modelName, self::SEARCH, [$parameters], $options);
    }

    /**
     * Read record(s).
     *
     * @param array|int $ids
     */
    public function read($ids, array $options = []): array
    {
        return (array) $this->manager
            ->getClient()
            ->call($this->modelName, self::READ, (array) $ids, $options);
    }

    /**
     * Search and read record(s).
     */
    public function findBy(array $parameters = [], array $options = []): array
    {
        return (array) $this->manager
            ->getClient()
            ->call($this->modelName, self::SEARCH_READ, [$parameters], $options);
    }

    /**
     * Update record(s).
     *
     * @param array|int $ids
     */
    public function update($ids, array $data = []): self
    {
        $this->manager
            ->getClient()
            ->call($this->modelName, self::WRITE, [(array) $ids, $data]);

        return $this;
    }

    /**
     * Count records.
     */
    public function count(array $parameters = []): int
    {
        return (int) $this->manager
            ->getClient()
            ->call($this->modelName, self::SEARCH_COUNT, [$parameters]);
    }

    /**
     * Delete record(s).
     *
     * @param array|int $ids
     */
    public function delete($ids): self
    {
        $this->manager
            ->getClient()
            ->call($this->modelName, self::DELETE, [(array) $ids]);

        return $this;
    }

    /**
     * List record fields.
     */
    public function listFields(array $options = []): array
    {
        return (array) $this->manager
            ->getClient()
            ->call($this->modelName, self::LIST_FIELDS, [], $options);
    }

    public function getManager(): Manager
    {
        return $this->manager;
    }

    public function setManager(Manager $manager): self
    {
        $this->manager = $manager;

        return $this;
    }

    public function getModelName(): string
    {
        return $this->modelName;
    }

    public function setModelName(string $modelName): self
    {
        $this->modelName = $modelName;

        return $this;
    }
}
