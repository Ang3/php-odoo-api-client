<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-api-client
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Repository;

use Ang3\Component\Odoo\DBAL\Expression\Domain\CompositeDomain;
use Ang3\Component\Odoo\DBAL\Expression\Domain\DomainInterface;
use Ang3\Component\Odoo\DBAL\Expression\ExpressionBuilder;
use Ang3\Component\Odoo\DBAL\Query\QueryBuilder;
use Ang3\Component\Odoo\DBAL\RecordManager;
use Ang3\Component\Odoo\DBAL\Schema\Model;

/**
 * @author Joanis ROUANET <https://github.com/Ang3>
 */
class RecordRepository
{
    public function __construct(private RecordManager $recordManager, private readonly string $modelName)
    {
        $recordManager->addRepository($this);
    }

    /**
     * Gets the model metadata from the schema.
     */
    public function getMetadata(): Model
    {
        return $this->recordManager->getSchema()->getModel($this->modelName);
    }

    /**
     * Insert a new record.
     *
     * @return int the ID of the new record
     *
     * @throws \InvalidArgumentException when $data is empty
     */
    public function insert(array $data): int
    {
        if (!$data) {
            throw new \InvalidArgumentException('Data cannot be empty');
        }

        $result = $this
            ->createQueryBuilder()
            ->insert()
            ->setValues($data)
            ->getQuery()
            ->execute()
        ;

        return \is_scalar($result) ? (int) $result : 0;
    }

    /**
     * Update record(s).
     *
     * NB: It is not currently possible to perform “computed” updates
     * (where the value being set depends on an existing value of a record).
     */
    public function update(int|array $ids, array $data = []): void
    {
        if (!$data) {
            return;
        }

        $this
            ->createQueryBuilder()
            ->update((array) $ids)
            ->setValues($data)
            ->getQuery()
            ->execute()
        ;
    }

    /**
     * Delete record(s).
     */
    public function delete(int|array $ids): void
    {
        if (!$ids) {
            return;
        }

        $this
            ->createQueryBuilder()
            ->delete((array) $ids)
            ->getQuery()
            ->execute()
        ;
    }

    /**
     * Search one ID of record by criteria.
     */
    public function searchOne(DomainInterface|array|null $criteria): ?int
    {
        return (int) $this
            ->createQueryBuilder()
            ->search()
            ->where($this->normalizeCriteria($criteria))
            ->getQuery()
            ->getOneOrNullScalarResult()
        ;
    }

    /**
     * Search all ID of record(s).
     *
     * @return int[]
     */
    public function searchAll(array $orders = [], int $limit = null, int $offset = null): array
    {
        return $this->search(null, $orders, $limit, $offset);
    }

    /**
     * Search ID of record(s) by criteria.
     *
     * @return int[]
     */
    public function search(DomainInterface|array|null $criteria = null, array $orders = [], int $limit = null, int $offset = null): array
    {
        return $this
            ->createQueryBuilder()
            ->search()
            ->where($this->normalizeCriteria($criteria))
            ->setOrders($orders)
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getScalarResult()
        ;
    }

    /**
     * Find ONE record by ID.
     *
     * @throws RecordNotFoundException when the record was not found
     */
    public function read(int $id, array $fields = []): array
    {
        $record = $this->find($id, $fields);

        if (!$record) {
            throw new RecordNotFoundException($this->modelName, $id);
        }

        return $record;
    }

    /**
     * Find ONE record by ID.
     */
    public function find(int $id, array $fields = []): ?array
    {
        return $this->findOneBy($this->expr()->eq('id', $id), $fields);
    }

    /**
     * Find ONE record by criteria.
     */
    public function findOneBy(DomainInterface|array|null $criteria = null, array $fields = [], array $orders = [], int $offset = null): ?array
    {
        $result = $this->findBy($criteria, $fields, $orders, 1, $offset);

        return array_pop($result);
    }

    /**
     * Find all records.
     *
     * @return array[]
     */
    public function findAll(array $fields = [], array $orders = [], int $limit = null, int $offset = null): array
    {
        return $this->findBy(null, $fields, $orders, $limit, $offset);
    }

    /**
     * Find record(s) by criteria.
     *
     * @return array[]
     */
    public function findBy(DomainInterface|array|null $criteria = null, array $fields = [], array $orders = [], int $limit = null, int $offset = null): array
    {
        return $this
            ->createQueryBuilder()
            ->select($fields)
            ->where($this->normalizeCriteria($criteria))
            ->setOrders($orders)
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Check if a record exists.
     */
    public function exists(int $id): bool
    {
        return 1 === $this->count($this->expr()->eq('id', $id));
    }

    /**
     * Count number of all records for the model.
     */
    public function countAll(): int
    {
        return $this->count();
    }

    /**
     * Count number of records for a model and criteria.
     */
    public function count(DomainInterface|array|null $criteria = null): int
    {
        return $this
            ->createQueryBuilder()
            ->select()
            ->where($this->normalizeCriteria($criteria))
            ->getQuery()
            ->count()
        ;
    }

    public function createQueryBuilder(): QueryBuilder
    {
        return $this->recordManager
            ->createQueryBuilder($this->modelName)
            ->select()
        ;
    }

    public function setRecordManager(RecordManager $recordManager): self
    {
        $this->recordManager = $recordManager;

        return $this;
    }

    public function getRecordManager(): RecordManager
    {
        return $this->recordManager;
    }

    public function getModelName(): string
    {
        return $this->modelName;
    }

    public function expr(): ExpressionBuilder
    {
        return $this->recordManager->getExpressionBuilder();
    }

    public function normalizeCriteria(DomainInterface|array|null $criteria = null): ?DomainInterface
    {
        return \is_array($criteria) ? CompositeDomain::criteria($criteria) : $criteria;
    }
}
