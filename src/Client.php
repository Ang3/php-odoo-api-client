<?php

namespace Ang3\Component\Odoo;

use Ang3\Component\Odoo\Exception\AuthenticationException;
use Ang3\Component\Odoo\Exception\MissingConfigParameterException;
use Ang3\Component\Odoo\Expression\DomainInterface;
use Ang3\Component\Odoo\Expression\ExpressionBuilder;
use Ang3\Component\XmlRpc\Exception\RequestException;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;

class Client
{
    /**
     * Endpoints.
     */
    public const ENDPOINT_COMMON = 'xmlrpc/2/common';
    public const ENDPOINT_OBJECT = 'xmlrpc/2/object';

    /**
     * ORM methods.
     */
    const CREATE = 'create';
    const READ = 'read';
    const UPDATE = 'write';
    const DELETE = 'unlink';
    const FIND = 'search_read';
    const SEARCH = 'search';
    const COUNT = 'search_count';
    const LIST_FIELDS = 'fields_get';

    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $database;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    /**
     * @var Endpoint
     */
    private $commonEndpoint;

    /**
     * @var Endpoint
     */
    private $objectEndpoint;

    /**
     * @var ExpressionBuilder
     */
    private $expressionBuilder;

    /**
     * @var LoggerInterface|null
     */
    private $logger;

    /**
     * @var int|null
     */
    private $uid;

    public function __construct(string $url, string $database, string $username, string $password, LoggerInterface $logger = null)
    {
        $this->url = $url;
        $this->database = $database;
        $this->username = $username;
        $this->password = $password;
        $this->expressionBuilder = new ExpressionBuilder();
        $this->logger = $logger;
        $this->initEndpoints();
    }

    /**
     * Create a new client instance from array configuration.
     * The configuration array must have keys "url", "database", "username" and "password".
     *
     * @static
     *
     * @throws MissingConfigParameterException when a required parameter is missing
     */
    public static function createFromConfig(array $config, LoggerInterface $logger = null): self
    {
        $getParam = static function ($config, $paramName, $paramKey) {
            $value = $config[$paramName] ?? $config[$paramKey] ?? null;

            if (null === $value) {
                throw new MissingConfigParameterException(sprintf('Missing config parameter name "%s" or parameter key %d', $paramName, $paramKey));
            }

            return $value;
        };

        $url = $getParam($config, 'url', 0);
        $database = $getParam($config, 'database', 1);
        $username = $getParam($config, 'username', 2);
        $password = $getParam($config, 'password', 3);

        return new self($url, $database, $username, $password, $logger);
    }

    /**
     * Create a new record.
     *
     * @throws InvalidArgumentException when $data is empty
     * @throws RequestException         when request failed
     *
     * @return int the ID of the new record
     */
    public function create(string $modelName, array $data): int
    {
        if (!$data) {
            throw new InvalidArgumentException('Data cannot be empty');
        }

        return (int) $this->call($modelName, self::CREATE, [$data]);
    }

    /**
     * Read records.
     *
     * @param array|int $ids
     *
     * @throws RequestException when request failed
     */
    public function read(string $modelName, $ids, array $options = []): array
    {
        return (array) $this->call($modelName, self::READ, (array) $ids, $options);
    }

    /**
     * Update a record(s).
     *
     * @param array|int $ids
     *
     * @throws RequestException when request failed
     */
    public function update(string $modelName, $ids, array $data = []): void
    {
        if (!$data) {
            return;
        }

        $this->call($modelName, self::UPDATE, [(array) $ids, $data]);
    }

    /**
     * Delete record(s).
     *
     * @param array|int $ids
     *
     * @throws RequestException when request failed
     */
    public function delete(string $modelName, $ids): void
    {
        $this->call($modelName, self::DELETE, [(array) $ids]);
    }

    /**
     * Search one ID of record by criteria and options.
     *
     * @param DomainInterface|array|null $criteria
     *
     * @throws InvalidArgumentException when $criteria value is not valid
     * @throws RequestException         when request failed
     */
    public function searchOne(string $modelName, $criteria = null): ?int
    {
        $options['limit'] = 1;

        $result = $this->search($modelName, $this->expressionBuilder->normalizeDomains($criteria), $options);

        return array_shift($result);
    }

    /**
     * Search all ID of record(s) with options.
     *
     * @throws InvalidArgumentException when $criteria value is not valid
     * @throws RequestException         when request failed
     *
     * @return array<int>
     */
    public function searchAll(string $modelName, array $options = []): array
    {
        return $this->search($modelName, null, $options);
    }

    /**
     * Find ID of record(s) by criteria and options.
     *
     * @param DomainInterface|array|null $criteria
     *
     * @throws InvalidArgumentException when $criteria value is not valid
     * @throws RequestException         when request failed
     *
     * @return array<int>
     */
    public function search(string $modelName, $criteria = null, array $options = []): array
    {
        if (array_key_exists('fields', $options)) {
            unset($options['fields']);
        }

        return (array) $this->call($modelName, self::SEARCH, $this->expressionBuilder->normalizeDomains($criteria), $options);
    }

    /**
     * Find ONE record by ID and options.
     *
     * @throws RequestException when request failed
     */
    public function find(string $modelName, int $id, array $options = []): ?array
    {
        return $this->findOneBy($modelName, $this->expr()->eq('id', $id), $options);
    }

    /**
     * Find ONE record by criteria and options.
     *
     * @param DomainInterface|array|null $criteria
     *
     * @throws InvalidArgumentException when $criteria value is not valid
     * @throws RequestException         when request failed
     */
    public function findOneBy(string $modelName, $criteria = null, array $options = []): ?array
    {
        $result = $this->findBy($modelName, $criteria, $options);

        return array_pop($result);
    }

    /**
     * Find all record(s) with options.
     *
     * @throws RequestException when request failed
     *
     * @return array<int, array>
     */
    public function findAll(string $modelName, array $options = []): array
    {
        return $this->findBy($modelName, null, $options);
    }

    /**
     * Find record(s) by criteria and options.
     *
     * @param DomainInterface|array|null $criteria
     *
     * @throws InvalidArgumentException when $criteria value is not valid
     * @throws RequestException         when request failed
     *
     * @return array<int, array>
     */
    public function findBy(string $modelName, $criteria = null, array $options = []): array
    {
        return (array) $this->call($modelName, self::FIND, $this->expressionBuilder->normalizeDomains($criteria), $options);
    }

    /**
     * Check if a record exists.
     *
     * @throws RequestException when request failed
     */
    public function exists(string $modelName, int $id): bool
    {
        return 1 === $this->count($modelName, $this->expressionBuilder->normalizeDomains($this->expressionBuilder->eq('id', $id)));
    }

    /**
     * Count number of records for a model and criteria.
     *
     * @param DomainInterface|array|null $criteria
     *
     * @throws InvalidArgumentException when $criteria value is not valid
     * @throws RequestException         when request failed
     */
    public function count(string $modelName, $criteria = null): int
    {
        return (int) $this->call($modelName, self::COUNT, $this->expressionBuilder->normalizeDomains($criteria));
    }

    /**
     * List model fields.
     *
     * @throws RequestException when request failed
     */
    public function listFields(string $modelName, array $options = []): array
    {
        return (array) $this->call($modelName, self::LIST_FIELDS, [], $options);
    }

    /**
     * @throws RequestException when request failed
     *
     * @return mixed
     */
    public function call(string $name, string $method, array $parameters = [], array $options = [])
    {
        $loggerContext = [
            'request_id' => uniqid('xmlrpc-request-', true),
            'name' => $name,
            'method' => $method,
            'parameters' => $parameters,
            'options' => $options,
        ];

        if ($this->logger) {
            $this->logger->info('Calling method {name}::{method}', $loggerContext);
        }

        $result = $this->objectEndpoint->call('execute_kw', [
            $this->database,
            $this->authenticate(),
            $this->password,
            $name,
            $method,
            $parameters,
            $options,
        ]);

        if ($this->logger) {
            $loggedResult = is_scalar($result) ? $result : json_encode($result);
            $this->logger->debug(sprintf('Request result: %s', $loggedResult), [
                'request_id' => $loggerContext['request_id'],
            ]);
        }

        return $result;
    }

    public function version(): array
    {
        return $this->commonEndpoint->call('version');
    }

    /**
     * @throws AuthenticationException when authentication failed
     * @throws RequestException        when endpoint request failed
     */
    public function authenticate(): int
    {
        if (null === $this->uid) {
            $this->uid = $this->commonEndpoint->call('authenticate', [
                $this->database,
                $this->username,
                $this->password,
                [],
            ]);

            if (!$this->uid || !is_int($this->uid)) {
                throw new AuthenticationException();
            }
        }

        return $this->uid;
    }

    public function getIdentifier(): string
    {
        $database = preg_replace('([^a-zA-Z0-9_])', '_', $this->database);
        $user = preg_replace('([^a-zA-Z0-9_])', '_', $this->username);

        return sprintf('%s.%s.%s', sha1($this->url), $database, $user);
    }

    public function expr(): ExpressionBuilder
    {
        return $this->expressionBuilder;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;
        $this->initEndpoints();

        return $this;
    }

    public function getDatabase(): string
    {
        return $this->database;
    }

    public function setDatabase(string $database): self
    {
        $this->database = $database;

        return $this;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getCommonEndpoint(): Endpoint
    {
        return $this->commonEndpoint;
    }

    public function getObjectEndpoint(): Endpoint
    {
        return $this->objectEndpoint;
    }

    public function getExpressionBuilder(): ExpressionBuilder
    {
        return $this->expressionBuilder;
    }

    public function getLogger(): ?LoggerInterface
    {
        return $this->logger;
    }

    public function setLogger(?LoggerInterface $logger): self
    {
        $this->logger = $logger;
        $this->initEndpoints();

        return $this;
    }

    /**
     * @internal
     */
    private function initEndpoints(): void
    {
        $this->commonEndpoint = new Endpoint($this->url.'/'.self::ENDPOINT_COMMON);
        $this->objectEndpoint = new Endpoint($this->url.'/'.self::ENDPOINT_OBJECT);
    }
}
