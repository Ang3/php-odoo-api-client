<?php

namespace Ang3\Component\Odoo;

use Ang3\Component\Odoo\DBAL\Expression\ExpressionBuilderAwareTrait;
use Ang3\Component\Odoo\DBAL\Query\OrmQuery;
use Ang3\Component\Odoo\DBAL\RecordManager;
use Ang3\Component\Odoo\Exception\AuthenticationException;
use Ang3\Component\Odoo\Exception\MissingConfigParameterException;
use Ang3\Component\Odoo\Exception\RequestException;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;

/**
 * After multiple search through Odoo forums, documentation and pastebins, I found that Odoo JSON RPC requests wait for :
 * {
 * "jsonrpc": "2.0",
 * "method": "call",
 * "id": "rpc61eb4703a66c38.90127030",
 * "params": {
 * "service": "object",
 * "method": "execute",
 * "args": [
 * "__DATABASE__",
 * __USERID__,
 * "__PASWORD__",
 * "__MODEL__",
 * "__METHOD",
 * [__DOMAIN__],
 * [__FIELDS__],
 * __LIMIT,
 * __OFFSET,
 * "__ORDER__",
 * __CONTEXT_OBJECT__
 * ]
 * }
 * }
 */

class Client
{
    use ExpressionBuilderAwareTrait;

    /**
     * Endpoints.
     */
    public const ENDPOINT = 'jsonrpc';

    /**
     * Special commands.
     */
    public const LIST_FIELDS = 'fields_get';

    /**
     * URL of the database.
     *
     * @var string
     */
    private $url;

    /**
     * Name of the database.
     *
     * @var string
     */
    private $database;

    /**
     * Username of internal user.
     *
     * @var string
     */
    private $username;

    /**
     * Password of internal user.
     *
     * @var string
     */
    private $password;

    /**
     * COMMON endpoint.
     *
     * @var Endpoint
     */
    private $endpoint;

    /**
     * ORM record manager.
     *
     * @var RecordManager
     */
    private $recordManager;

    /**
     * Optional logger.
     *
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
        $this->recordManager = new RecordManager($this);
        $this->logger = $logger;
        $this->initEndpoint();
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

        return (int) $this->call($modelName, OrmQuery::CREATE, [$data]);
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
        $ids = [is_int($ids) ? [$ids] : (array) $ids];

        return (array) $this->call($modelName, OrmQuery::READ, $ids, $options);
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

        $this->call($modelName, OrmQuery::WRITE, [(array) $ids, $data]);
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
        $ids = is_array($ids) ? $ids : [(int) $ids];
        $this->call($modelName, OrmQuery::UNLINK, [$ids]);
    }

    /**
     * Search one ID of record by criteria and options.
     *
     * @throws InvalidArgumentException when $criteria value is not valid
     * @throws RequestException         when request failed
     */
    public function searchOne(string $modelName, iterable $criteria = null, array $options = []): ?int
    {
        $options['limit'] = 1;
        $result = $this->search($modelName, $criteria, $options);

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
        $options['fields'] = ['id'];

        return array_column($this->findBy($modelName, null, $options), 'id');
    }

    /**
     * Find ID of record(s) by criteria and options.
     *
     * @throws InvalidArgumentException when $criteria value is not valid
     * @throws RequestException         when request failed
     *
     * @return array<int>
     */
    public function search(string $modelName, iterable $criteria = null, array $options = []): array
    {
        if (array_key_exists('fields', $options)) {
            unset($options['fields']);
        }

        return (array) $this->call($modelName, OrmQuery::SEARCH, $this->expr()->normalizeDomains($criteria), $options);
    }

    /**
     * Find ONE record by ID and options.
     *
     * @throws RequestException when request failed
     */
    public function find(string $modelName, int $id, array $options = []): ?array
    {
        return $this->findOneBy($modelName, [
            'id' => $id,
        ], $options);
    }

    /**
     * Find ONE record by criteria and options.
     *
     * @throws InvalidArgumentException when $criteria value is not valid
     * @throws RequestException         when request failed
     */
    public function findOneBy(string $modelName, iterable $criteria = null, array $options = []): ?array
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
     * @throws InvalidArgumentException when $criteria value is not valid
     * @throws RequestException         when request failed
     *
     * @return array<int, array>
     */
    public function findBy(string $modelName, iterable $criteria = null, array $options = []): array
    {
        return (array) $this->call($modelName, OrmQuery::SEARCH_READ, $this->expr()->normalizeDomains($criteria), $options);
    }

    /**
     * Check if a record exists.
     *
     * @throws RequestException when request failed
     */
    public function exists(string $modelName, int $id): bool
    {
        return 1 === $this->count($modelName, [
            'id' => $id,
        ]);
    }

    /**
     * Count all records for a model.
     *
     * @throws InvalidArgumentException when $criteria value is not valid
     * @throws RequestException         when request failed
     */
    public function countAll(string $modelName): int
    {
        return $this->count($modelName);
    }

    /**
     * Count number of records for a model and criteria.
     *
     * @throws InvalidArgumentException when $criteria value is not valid
     * @throws RequestException         when request failed
     */
    public function count(string $modelName, iterable $criteria = null): int
    {
        return (int) $this->call($modelName, OrmQuery::SEARCH_COUNT, $this->expr()->normalizeDomains($criteria));
    }

    /**
     * List model fields.
     *
     * @deprecated since version 7.0 and will be removed in 8.0, use the record manager schema instead.
     */
    public function listFields(string $modelName, array $options = []): array
    {
        return (array) $this->call($modelName, self::LIST_FIELDS, [], $options);
    }

    /**
     * @return mixed
     */
    public function call(string $name, string $method, array $parameters = [], array $options = [])
    {
        $loggerContext = [
            'request_id' => uniqid('rpc', true),
            'name' => $name,
            'method' => $method,
            'parameters' => $parameters,
            'options' => $options,
        ];

        if ($this->logger) {
            $this->logger->debug('Calling method {name}::{method}', $loggerContext);
        }

        $result = $this->endpoint->call('object', 'execute', [
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
        return $this->endpoint->call('common', 'version');
    }

    /**
     * @throws AuthenticationException when authentication failed
     */
    public function authenticate(): int
    {
        if (null === $this->uid) {
            $this->uid = $this->endpoint->call('common', 'login', [$this->database, $this->username, $this->password]);

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

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;
        $this->initEndpoint();

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
        return $this->endpoint;
    }

    public function getRecordManager(): RecordManager
    {
        return $this->recordManager;
    }

    public function getLogger(): ?LoggerInterface
    {
        return $this->logger;
    }

    public function setLogger(?LoggerInterface $logger): self
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * @internal
     */
    private function initEndPoint(): void
    {
        $this->endpoint = new Endpoint($this->url.'/'.self::ENDPOINT);
    }
}
