<?php

namespace Ang3\Component\Odoo;

class Manager
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var Repository[]
     */
    private $repositories = [];

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Create a record.
     */
    public function create(string $modelName, array $data = []): int
    {
        return $this
            ->getRepository($modelName)
            ->insert($data);
    }

    /**
     * Search models.
     */
    public function search(string $modelName, array $parameters = [], array $options = []): array
    {
        return $this
            ->getRepository($modelName)
            ->search($parameters, $options);
    }

    /**
     * Search and read models.
     */
    public function find(string $modelName, int $id, array $options = []): array
    {
        return $this
            ->getRepository($modelName)
            ->findBy([
                'id' => $id,
            ], $options);
    }

    /**
     * Update a record.
     *
     * @param array|int $ids
     */
    public function update(string $modelName, $ids, array $data = []): self
    {
        $this
            ->getRepository($modelName)
            ->update($ids, $data);

        return $this;
    }

    /**
     * Delete models.
     *
     * @param array|int $ids
     */
    public function delete(string $modelName, $ids): self
    {
        $this
            ->getRepository($modelName)
            ->delete($ids);

        return $this;
    }

    public function getRepository(string $modelName, bool $ignoreCache = false, bool $reloadCache = false): Repository
    {
        if ($ignoreCache || !isset($this->repositories[$modelName])) {
            $repository = new Repository($this, $modelName);

            if ($ignoreCache && !$reloadCache) {
                return $repository;
            }

            $this->registerRepository($repository);
        }

        return $this->repositories[$modelName];
    }

    /**
     * @return Repository[]
     */
    public function getRepositories(): array
    {
        return $this->repositories;
    }

    public function setRepositories(array $repositories = []): self
    {
        $this->repositories = [];

        foreach ($repositories as $repository) {
            $this->registerRepository($repository);
        }

        return $this;
    }

    public function registerRepository(Repository $repository): self
    {
        $this->repositories[$repository->getModelName()] = $repository;

        return $this;
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function setClient(Client $client): self
    {
        $this->client = $client;

        return $this;
    }
}
