<?php

namespace Ang3\Component\Odoo\DBAL\Query;

class OrmQuery extends AbstractQuery implements QueryInterface
{
    /**
     * Query ORM methods.
     */
    const CREATE = 'create';
    const WRITE = 'write';
    const READ = 'read';
    const UNLINK = 'unlink';
    const SEARCH_READ = 'search_read';
    const SEARCH = 'search';
    const SEARCH_COUNT = 'search_count';

    /**
     * List of query ORM methods.
     *
     * @var string[]
     */
    protected static $methods = [
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
    public function setMethod(string $method): self
    {
        if (!in_array($method, self::$methods)) {
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
        if (!in_array($this->method, [self::SEARCH, self::SEARCH_READ])) {
            throw new QueryException(sprintf('You can count results with method "%s" and "%s" only.', self::SEARCH, self::SEARCH_READ));
        }

        $query = new self($this->recordManager, $this->name, self::SEARCH_COUNT);
        $query->setParameters($this->recordManager->getExpressionBuilder()->normalizeDomains($this->parameters));

        return (int) $query->execute();
    }

    /**
     * Gets just ONE scalar result.
     * Allowed methods: SEARCH, SEARCH_READ.
     *
     * @return bool|int|float|string
     *
     * @throws NoUniqueResultException on no unique result
     * @throws NoResultException       on no result
     * @throws QueryException          on invalid query method
     */
    public function getSingleScalarResult()
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
     * @return bool|int|float|string|null
     *
     * @throws NoUniqueResultException on no unique result
     * @throws QueryException          on invalid query method
     */
    public function getOneOrNullScalarResult()
    {
        $result = $this->getScalarResult();

        if (count($result) > 1) {
            throw new NoUniqueResultException();
        }

        return array_shift($result);
    }

    /**
     * Gets a list of scalar result.
     * Allowed methods: SEARCH, SEARCH_READ.
     *
     * @throws QueryException on invalid query method
     *
     * @return array<bool|int|float|string>
     */
    public function getScalarResult(): array
    {
        $result = $this->getResult();

        foreach ($result as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $fieldName => $fieldValue) {
                    $value[$fieldName] = is_array($fieldValue) ? ($fieldValue[0] ?? array_shift($fieldValue)) : $fieldValue;
                }

                $value = array_shift($value);
            }

            $result[$key] = $value;
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
        $result = array_shift($result);

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

        if (count($result) > 1) {
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
        if (!in_array($this->method, [self::SEARCH, self::SEARCH_READ])) {
            throw new QueryException(sprintf('You can get results with methods "%s" and "%s" only.', self::SEARCH, self::SEARCH_READ));
        }

        return (array) $this->execute();
    }
}
