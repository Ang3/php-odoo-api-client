<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-api-client
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Query;

use Ang3\Component\Odoo\DBAL\Expression\Domain\DomainInterface;
use Ang3\Component\Odoo\DBAL\Expression\Exception\ConversionException;
use Ang3\Component\Odoo\DBAL\Expression\ExpressionBuilder;
use Ang3\Component\Odoo\DBAL\RecordManager;

class QueryBuilder
{
    /**
     * Query methods.
     */
    public const SELECT = 'select';
    public const SEARCH = 'search';
    public const INSERT = 'insert';
    public const UPDATE = 'update';
    public const DELETE = 'delete';

    /**
     * The type of query this is. Can be select, search, insert, update or delete.
     */
    private string $type = self::SELECT;

    /**
     * @var string[]
     */
    private array $select = [];
    private array $ids = [];
    private array $values = [];
    private ?DomainInterface $where = null;
    private array $orders = [];
    private ?int $maxResults = null;
    private ?int $firstResult = null;

    public function __construct(private readonly RecordManager $recordManager, private string $from)
    {
    }

    /**
     * Defines the query of type "SELECT" with selected fields.
     * No fields selected = all fields returned.
     *
     * @param array|string|null $fields
     */
    public function select($fields = null): self
    {
        $this->type = self::SELECT;
        $this->select = [];
        $this->values = [];
        $this->ids = [];

        $fields = $fields ? (array) $fields : [];

        foreach ($fields as $fieldName) {
            $this->addSelect($fieldName);
        }

        return $this;
    }

    /**
     * Defines the query of type "SEARCH".
     */
    public function search(): self
    {
        $this->type = self::SEARCH;
        $this->select = [];
        $this->values = [];
        $this->ids = [];

        return $this;
    }

    /**
     * Defines the query of type "INSERT".
     */
    public function insert(): self
    {
        $this->type = self::INSERT;
        $this->select = [];
        $this->ids = [];
        $this->where = null;

        return $this;
    }

    /**
     * Defines the query of type "UPDATE" with ids of records to update.
     *
     * @param int[] $ids
     */
    public function update(array $ids): self
    {
        $this->type = self::UPDATE;
        $this->select = [];

        return $this->setIds($ids);
    }

    /**
     * Defines the query of type "DELETE" with ids of records to delete.
     */
    public function delete(array $ids): self
    {
        $this->type = self::DELETE;
        $this->select = [];
        $this->values = [];

        return $this->setIds($ids);
    }

    /**
     * Adds a field to select.
     *
     * @throws QueryException when the type of the query is not "SELECT"
     */
    public function addSelect(string $fieldName): self
    {
        if (self::SELECT !== $this->type) {
            throw new QueryException('You can select fields in query of type "SELECT" only.');
        }

        if (!\in_array($fieldName, $this->select, true)) {
            $this->select[] = $fieldName;
        }

        return $this;
    }

    /**
     * Gets selected fields.
     */
    public function getSelect(): array
    {
        return $this->select;
    }

    /**
     * Sets the target model name.
     */
    public function from(string $modelName): self
    {
        $this->from = $modelName;

        return $this;
    }

    /**
     * Gets the target model name of the query.
     */
    public function getFrom(): ?string
    {
        return $this->from;
    }

    /**
     * Sets target IDs in case of query of type "UPDATE" or "DELETE".
     *
     * @throws QueryException when the type of the query is not "UPDATE" nor "DELETE"
     */
    public function setIds(array $ids): self
    {
        $this->ids = [];

        foreach ($ids as $id) {
            $this->addId($id);
        }

        return $this;
    }

    /**
     * Adds target ID in case of query of type "UPDATE" or "DELETE".
     *
     * @throws QueryException when the type of the query is not "UPDATE" nor "DELETE"
     */
    public function addId(int $id): self
    {
        if (!\in_array($this->type, [self::UPDATE, self::DELETE], true)) {
            throw new QueryException('You can set indexes in query of type "UPDATE" or "DELETE" only.');
        }

        if (!\in_array($id, $this->ids, true)) {
            $this->ids[] = $id;
        }

        return $this;
    }

    /**
     * Sets field values in case of query of type "INSERT" or "UPDATE".
     *
     * @throws QueryException when the type of the query is not "INSERT" nor "UPDATE"
     */
    public function setValues(array $values = []): self
    {
        $this->values = [];

        foreach ($values as $fieldName => $value) {
            $this->set($fieldName, $value);
        }

        return $this;
    }

    /**
     * Set a field value in case of query of type "INSERT" or "UPDATE".
     *
     * @param mixed $value
     *
     * @throws QueryException when the type of the query is not "INSERT" nor "UPDATE"
     */
    public function set(string $fieldName, $value): self
    {
        if (!\in_array($this->type, [self::INSERT, self::UPDATE], true)) {
            throw new QueryException('You can set values in query of type "INSERT" or "UPDATE" only.');
        }

        $this->values[$fieldName] = $value;

        return $this;
    }

    /**
     * Gets field values set in case of query of type "INSERT" or "UPDATE".
     */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * Sets criteria for queries of type "SELECT" and "SEARCH".
     *
     * @throws QueryException when the type of the query is not "SELECT" not "SEARCH"
     */
    public function where(?DomainInterface $domain = null): self
    {
        $this->assertSupportsWhereClause();
        $this->where = $domain;

        return $this;
    }

    /**
     * Takes the WHERE clause and adds a node with logical operator AND.
     *
     * @throws QueryException when the type of the query is not "SELECT" nor "SEARCH"
     */
    public function andWhere(DomainInterface $domain): self
    {
        $this->assertSupportsWhereClause();
        $this->where = $this->where ? $this->expr()->andX($this->where, $domain) : $domain;

        return $this;
    }

    /**
     * Takes the WHERE clause and adds a node with logical operator OR.
     *
     * @throws QueryException when the type of the query is not "SELECT" nor "SEARCH"
     */
    public function orWhere(DomainInterface $domain): self
    {
        $this->assertSupportsWhereClause();
        $this->where = $this->where ? $this->expr()->orX($this->where, $domain) : $domain;

        return $this;
    }

    /**
     * Gets the WHERE clause.
     */
    public function getWhere(): ?DomainInterface
    {
        return $this->where;
    }

    /**
     * @internal
     *
     * @throws QueryException when the type of the query is not "SELECT" nor "SEARCH"
     */
    private function assertSupportsWhereClause(): void
    {
        if (!\in_array($this->type, [self::SELECT, self::SEARCH], true)) {
            throw new QueryException('You can set criteria in query of type "SELECT" or "SEARCH" only.');
        }
    }

    /**
     * Sets orders.
     */
    public function setOrders(array $orders = []): self
    {
        $this->orders = [];

        foreach ($orders as $fieldName => $isAsc) {
            $this->addOrderBy($fieldName, $isAsc);
        }

        return $this;
    }

    /**
     * Clears orders and adds one.
     */
    public function orderBy(string $fieldName, bool $isAsc = true): self
    {
        $this->orders = [];

        return $this->addOrderBy($fieldName, $isAsc);
    }

    /**
     * Adds order.
     *
     * @throws QueryException when the query type is not valid
     */
    public function addOrderBy(string $fieldName, bool $isAsc = true): self
    {
        if (!\in_array($this->type, [self::SELECT, self::SEARCH], true)) {
            throw new QueryException('You can set orders in query of type "SELECT", "SEARCH" only.');
        }

        $this->orders[$fieldName] = $isAsc;

        return $this;
    }

    /**
     * Gets ordered fields.
     */
    public function getOrders(): array
    {
        return $this->orders;
    }

    /**
     * Sets the max results of the query (limit).
     */
    public function setMaxResults(?int $maxResults): self
    {
        $this->maxResults = $maxResults;

        return $this;
    }

    /**
     * Gets the max results of the query.
     */
    public function getMaxResults(): ?int
    {
        return $this->maxResults;
    }

    /**
     * Sets the first results of the query (offset).
     */
    public function setFirstResult(?int $firstResult): self
    {
        $this->firstResult = $firstResult;

        return $this;
    }

    /**
     * Gets the first results of the query.
     */
    public function getFirstResult(): ?int
    {
        return $this->firstResult;
    }

    /**
     * Computes and returns the query.
     *
     * @throws QueryException      on invalid query
     * @throws ConversionException on data conversion failure
     */
    public function getQuery(): OrmQuery
    {
        $method = match ($this->type) {
            self::SELECT => OrmQuery::SEARCH_READ,
            self::SEARCH => OrmQuery::SEARCH,
            self::INSERT => OrmQuery::CREATE,
            self::UPDATE => OrmQuery::WRITE,
            self::DELETE => OrmQuery::UNLINK,
            default => throw new \InvalidArgumentException(sprintf('The query type "%s" is not valid.', $this->type)),
        };

        $query = new OrmQuery($this->recordManager, $this->from, $method);

        if (\in_array($this->type, [self::SELECT, self::SEARCH], true)) {
            $parameters = $this->expr()->normalizeDomains($this->where);
        } elseif (self::DELETE === $this->type) {
            if (!$this->ids) {
                throw new QueryException('You must set indexes for queries of type "DELETE".');
            }

            $parameters = [$this->ids];
        } else {
            if (!$this->values) {
                throw new QueryException('You must set values for queries of type "INSERT" and "UPDATE".');
            }

            $parameters = $this->expr()->normalizeData($this->values);

            if (self::UPDATE === $this->type) {
                if (!$this->ids) {
                    throw new QueryException('You must set indexes for queries of type "UPDATE".');
                }

                $parameters = [$this->ids, $parameters];
            }
        }

        $query->setParameters($parameters);

        if (\in_array($this->type, [self::SELECT, self::SEARCH], true)) {
            $options = [];

            if (self::SELECT === $this->type && $this->select) {
                $options['fields'] = $this->select;
            }

            $orders = $this->orders;

            if ($orders) {
                foreach ($orders as $fieldName => $isAsc) {
                    $orders[$fieldName] = sprintf('%s %s', $fieldName, $isAsc ? 'asc' : 'desc');
                }

                $options['order'] = implode(', ', $orders);
            }

            if ($this->firstResult) {
                $options['offset'] = $this->firstResult;
            }

            if ($this->maxResults) {
                $options['limit'] = $this->maxResults;
            }

            $query->setOptions($options);
        }

        return $query;
    }

    /**
     * Gets the type of the query.
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Gets the related manager of the query.
     */
    public function getRecordManager(): RecordManager
    {
        return $this->recordManager;
    }

    /**
     * Shortcut to the expression builder of the related client.
     */
    public function expr(): ExpressionBuilder
    {
        return $this->recordManager->getExpressionBuilder();
    }
}
