<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-api-client
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Query;

class OrmQuery extends AbstractQuery implements QueryInterface
{
    /**
     * Query ORM methods.
     */
    public const CREATE = 'create';
    public const WRITE = 'write';
    public const READ = 'read';
    public const UNLINK = 'unlink';
    public const SEARCH_READ = 'search_read';
    public const SEARCH = 'search';
    public const SEARCH_COUNT = 'search_count';

    /**
     * List of query ORM methods.
     *
     * @var string[]
     */
    protected static array $methods = [
        self::CREATE,
        self::WRITE,
        self::READ,
        self::UNLINK,
        self::SEARCH_READ,
        self::SEARCH,
        self::SEARCH_COUNT,
    ];

    /**
     * @throws QueryException when the ORM method is not valid
     */
    public function setMethod(string $method): static
    {
        if (!\in_array($method, self::$methods, true)) {
            throw new QueryException(sprintf('The ORM query method "%s" is not valid.', $method));
        }

        $this->method = $method;

        return $this;
    }

    /**
     * Counts the number of records from parameters.
     * Allowed methods: SEARCH, SEARCH_READ.
     *
     * @throws QueryException on invalid query method
     */
    public function count(): int
    {
        if (!\in_array($this->method, [self::SEARCH, self::SEARCH_READ], true)) {
            throw new QueryException(sprintf('You can count results with method "%s" and "%s" only.', self::SEARCH, self::SEARCH_READ));
        }

        $query = new self($this->recordManager, $this->name, self::SEARCH_COUNT);
        $query->setParameters($this->parameters);

        return (int) $query->execute();
    }

    /**
     * Gets just ONE scalar result.
     * Allowed methods: SEARCH, SEARCH_READ.
     *
     * @throws NoUniqueResultException on no unique result
     * @throws NoResultException       on no result
     * @throws QueryException          on invalid query method
     */
    public function getSingleScalarResult(): float|bool|int|string
    {
        $result = $this->getOneOrNullScalarResult();

        if (!$result) {
            throw new NoResultException();
        }

        return $result;
    }

    /**
     * Gets one or NULL scalar result.
     * Allowed methods: SEARCH, SEARCH_READ.
     *
     * @throws NoUniqueResultException on no unique result
     * @throws QueryException          on invalid query method
     */
    public function getOneOrNullScalarResult(): float|bool|int|string|null
    {
        $result = $this->getScalarResult();

        if (\count($result) > 1) {
            throw new NoUniqueResultException();
        }

        return array_shift($result);
    }

    /**
     * Gets a list of scalar result.
     * Allowed methods: SEARCH, SEARCH_READ.
     *
     * @return array<bool|int|float|string>
     *
     * @throws QueryException on invalid query method
     */
    public function getScalarResult(): array
    {
        $result = $this->getResult();

        if (self::SEARCH === $this->method) {
            return $result;
        }

        $selectedFields = $this->options['fields'] ?? [];
        if (\count($selectedFields) > 1) {
            throw new QueryException('More than one field selected.');
        }

        $selectedFieldName = $selectedFields[0] ?? 'id';

        foreach ($result as $key => $value) {
            $result[$key] = $value[$selectedFieldName] ?? null;
        }

        return $result;
    }

    /**
     * Gets one row.
     * Allowed methods: SEARCH, SEARCH_READ.
     *
     * @throws NoUniqueResultException on no unique result
     * @throws NoResultException       on no result
     * @throws QueryException          on invalid query method
     */
    public function getSingleResult(): array
    {
        $result = $this->getOneOrNullResult();
        $result = \is_array($result) ? array_shift($result) : null;

        if (!$result) {
            throw new NoResultException();
        }

        return $result;
    }

    /**
     * Gets one or NULL row.
     * Allowed methods: SEARCH, SEARCH_READ.
     *
     * @throws NoUniqueResultException on no unique result
     * @throws QueryException          on invalid query method
     */
    public function getOneOrNullResult(): ?array
    {
        $result = $this->getResult();

        if (\count($result) > 1) {
            throw new NoUniqueResultException();
        }

        return array_shift($result);
    }

    /**
     * Gets all result rows.
     * Allowed methods: SEARCH, SEARCH_READ.
     *
     * @throws QueryException on invalid query method
     */
    public function getResult(): array
    {
        if (!\in_array($this->method, [self::SEARCH, self::SEARCH_READ], true)) {
            throw new QueryException(sprintf('You can get results with methods "%s" and "%s" only.', self::SEARCH, self::SEARCH_READ));
        }

        return (array) $this->execute();
    }
}
