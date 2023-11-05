<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-api-client
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL;

use Ang3\Component\Odoo\Client;
use Ang3\Component\Odoo\DBAL\Expression\ExpressionBuilder;
use Ang3\Component\Odoo\DBAL\Query\NativeQuery;
use Ang3\Component\Odoo\DBAL\Query\OrmQuery;
use Ang3\Component\Odoo\DBAL\Query\QueryBuilder;
use Ang3\Component\Odoo\DBAL\Query\QueryInterface;
use Ang3\Component\Odoo\DBAL\Repository\RecordRepository;
use Ang3\Component\Odoo\DBAL\Schema\Schema;

/**
 * @author Joanis ROUANET <https://github.com/Ang3>
 */
class RecordManager
{
    private Schema $schema;
    private ExpressionBuilder $expressionBuilder;

    /**
     * @param RecordRepository[] $repositories
     */
    public function __construct(private readonly Client $client, private array $repositories = [])
    {
        $this->schema = new Schema($client);
        $this->expressionBuilder = new ExpressionBuilder();
    }

    public function getRepository(string $modelName): RecordRepository
    {
        if (!\array_key_exists($modelName, $this->repositories)) {
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

    public function createQueryBuilder(string $modelName): QueryBuilder
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

    public function executeQuery(QueryInterface $query): mixed
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

    public function getExpressionBuilder(): ExpressionBuilder
    {
        return $this->expressionBuilder;
    }
}
