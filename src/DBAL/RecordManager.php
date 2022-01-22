<?php

namespace Ang3\Component\Odoo\DBAL;

use Ang3\Component\Odoo\Client;
use Ang3\Component\Odoo\DBAL\Expression\DomainInterface;
use Ang3\Component\Odoo\DBAL\Expression\ExpressionBuilderAwareTrait;
use Ang3\Component\Odoo\DBAL\Query\NativeQuery;
use Ang3\Component\Odoo\DBAL\Query\OrmQuery;
use Ang3\Component\Odoo\DBAL\Query\QueryBuilder;
use Ang3\Component\Odoo\DBAL\Query\QueryInterface;
use Ang3\Component\Odoo\DBAL\Repository\RecordNotFoundException;
use Ang3\Component\Odoo\DBAL\Repository\RecordRepository;
use Ang3\Component\Odoo\DBAL\Schema\Schema;

class RecordManager
{
    use ExpressionBuilderAwareTrait;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var Schema
     */
    private $schema;

    /**
     * @var RecordRepository[]
     */
    private $repositories = [];

    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->schema = new Schema($client);
    }

    /**
     * Create a new record.
     *
     * @return int the ID of the new record
     */
    public function create(string $modelName, array $data): int
    {
        return $this
            ->getRepository($modelName)
            ->insert($data);
    }

    /**
     * Update record(s).
     *
     * NB: It is not currently possible to perform “computed” updates (by criteria).
     * To do it, you have to perform a search then an update with search result IDs.
     *
     * @param array|int $ids
     */
    public function update(string $modelName, $ids, array $data = []): void
    {
        $this
            ->getRepository($modelName)
            ->update($ids, $data);
    }

    /**
     * Delete record(s).
     *
     * NB: It is not currently possible to perform “computed” deletes (by criteria).
     * To do it, you have to perform a search then a delete with search result IDs.
     *
     * @param array|int $ids
     */
    public function delete(string $modelName, $ids): void
    {
        $this
            ->getRepository($modelName)
            ->delete($ids);
    }

    /**
     * Search one ID of record by criteria.
     */
    public function searchOne(string $modelName, ?DomainInterface $criteria): ?int
    {
        return $this
            ->getRepository($modelName)
            ->searchOne($criteria);
    }

    /**
     * Search all ID of record(s).
     *
     * @return int[]
     */
    public function searchAll(string $modelName, array $orders = [], int $limit = null, int $offset = null): array
    {
        return $this
            ->getRepository($modelName)
            ->searchAll($orders, $limit, $offset);
    }

    /**
     * Search ID of record(s) by criteria.
     *
     * @return int[]
     */
    public function search(string $modelName, ?DomainInterface $criteria = null, array $orders = [], int $limit = null, int $offset = null): array
    {
        return $this
            ->getRepository($modelName)
            ->search($criteria, $orders, $limit, $offset);
    }

    /**
     * Find ONE record by ID.
     *
     * @throws RecordNotFoundException when the record was not found
     */
    public function read(string $modelName, int $id, array $fields = []): array
    {
        return $this
            ->getRepository($modelName)
            ->read($id, $fields);
    }

    /**
     * Find ONE record by ID.
     */
    public function find(string $modelName, int $id, array $fields = []): ?array
    {
        return $this
            ->getRepository($modelName)
            ->find($id, $fields);
    }

    /**
     * Find ONE record by criteria.
     */
    public function findOneBy(string $modelName, ?DomainInterface $criteria = null, array $fields = [], array $orders = [], int $offset = null): ?array
    {
        return $this
            ->getRepository($modelName)
            ->findOneBy($criteria, $fields, $orders, $offset);
    }

    /**
     * Find all records.
     *
     * @return array[]
     */
    public function findAll(string $modelName, array $fields = [], array $orders = [], int $limit = null, int $offset = null): array
    {
        return $this
            ->getRepository($modelName)
            ->findAll($fields, $orders, $limit, $offset);
    }

    /**
     * Find record(s) by criteria.
     *
     * @return array[]
     */
    public function findBy(string $modelName, ?DomainInterface $criteria = null, array $fields = [], array $orders = [], int $limit = null, int $offset = null): array
    {
        return $this
            ->getRepository($modelName)
            ->findBy($criteria, $fields, $orders, $limit, $offset);
    }

    /**
     * Check if a record exists.
     */
    public function exists(string $modelName, int $id): bool
    {
        return $this
            ->getRepository($modelName)
            ->exists($id);
    }

    /**
     * Count number of all records for the model.
     */
    public function countAll(string $modelName): int
    {
        return $this
            ->getRepository($modelName)
            ->countAll();
    }

    /**
     * Count number of records for a model and criteria.
     */
    public function count(string $modelName, ?DomainInterface $criteria = null): int
    {
        return $this
            ->getRepository($modelName)
            ->count($criteria);
    }

    public function getRepository(string $modelName): RecordRepository
    {
        if (!array_key_exists($modelName, $this->repositories)) {
            $repository = new RecordRepository($this, $modelName);
            $this->addRepository($repository);

            return $repository;
        }

        return $this->repositories[$modelName];
    }

    public function setRepositories(array $repositories = []): self
    {
        $this->repositories = [];

        foreach ($repositories as $repository) {
            $this->addRepository($repository);
        }

        return $this;
    }

    public function addRepository(RecordRepository $repository): self
    {
        $this->repositories[$repository->getModelName()] = $repository;
        $repository->setRecordManager($this);

        return $this;
    }

    public function createQueryBuilder(string $modelName = null): QueryBuilder
    {
        return new QueryBuilder($this, $modelName);
    }

    public function createOrmQuery(string $name, string $method): OrmQuery
    {
        return new OrmQuery($this, $name, $method);
    }

    public function createNativeQuery(string $name, string $method): NativeQuery
    {
        return new NativeQuery($this, $name, $method);
    }

    /**
     * @return mixed
     */
    public function executeQuery(QueryInterface $query)
    {
        $options = $query->getOptions();

        if (!$options) {
            return $this->client->execute($query->getName(), $query->getMethod(), $query->getParameters());
        }

        return $this->client->execute($query->getName(), $query->getMethod(), $query->getParameters(), $options);
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function getSchema(): Schema
    {
        return $this->schema;
    }

    public function getRepositories(): array
    {
        return $this->repositories;
    }
}
