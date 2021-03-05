<?php

namespace Ang3\Component\Odoo;

use Ang3\Component\Odoo\DBAL\Expression\DomainInterface;
use Ang3\Component\Odoo\DBAL\RecordManager;
use Ang3\Component\Odoo\DBAL\Repository\RecordNotFoundException;
use Ang3\Component\Odoo\Exception\AuthenticationException;
use Ang3\Component\Odoo\Exception\MissingConfigParameterException;
use Psr\Log\LoggerInterface;

class Client
{
    /**
     * Endpoints.
     */
    public const ENDPOINT_COMMON = 'xmlrpc/2/common';
    public const ENDPOINT_OBJECT = 'xmlrpc/2/object';

    /**
     * Special commands.
     */
    const LIST_FIELDS = 'fields_get';

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
    private $commonEndpoint;

    /**
     * OBJECT endpoint.
     *
     * @var Endpoint
     */
    private $objectEndpoint;

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
     * @deprecated since version 7.0 and will be removed in 8.0, use the record manager instead.
     *
     * @return int the ID of the new record
     */
    public function create(string $modelName, array $data): int
    {
        return $this
            ->getRecordManager()
            ->getRepository($modelName)
            ->insert($data);
    }

    /**
     * Read records.
     *
     * @deprecated since version 7.0 and will be removed in 8.0, use the record manager instead.
     *
     * @throws RecordNotFoundException when the record was not found
     *
     * @param array|int $ids
     */
    public function read(string $modelName, $ids, array $options = []): array
    {
        return $this
            ->getRecordManager()
            ->getRepository($modelName)
            ->read($ids, $options['fields'] ?? []);
    }

    /**
     * Update a record(s).
     *
     * @deprecated since version 7.0 and will be removed in 8.0, use the record manager instead.
     *
     * @param array|int $ids
     */
    public function update(string $modelName, $ids, array $data = []): void
    {
        $this
            ->getRecordManager()
            ->getRepository($modelName)
            ->update($ids, $data);
    }

    /**
     * Delete record(s).
     *
     * @deprecated since version 7.0 and will be removed in 8.0, use the record manager instead.
     *
     * @param array|int $ids
     */
    public function delete(string $modelName, $ids): void
    {
        $this
            ->getRecordManager()
            ->getRepository($modelName)
            ->delete($ids);
    }

    /**
     * Search one ID of record by criteria and options.
     *
     * @deprecated since version 7.0 and will be removed in 8.0, use the record manager instead.
     *
     * @param DomainInterface|array|null $criteria
     */
    public function searchOne(string $modelName, $criteria): ?int
    {
        return $this
            ->getRecordManager()
            ->getRepository($modelName)
            ->searchOne($criteria);
    }

    /**
     * Search all ID of record(s) with options.
     *
     * @deprecated since version 7.0 and will be removed in 8.0, use the record manager instead.
     *
     * @return array<int>
     */
    public function searchAll(string $modelName, array $options = []): array
    {
        return $this
            ->getRecordManager()
            ->getRepository($modelName)
            ->searchAll($options['limit'] ?? null, $options['offset'] ?? null);
    }

    /**
     * Find ID of record(s) by criteria and options.
     *
     * @deprecated since version 7.0 and will be removed in 8.0, use the record manager instead.
     *
     * @param DomainInterface|array|null $criteria
     *
     * @return array<int>
     */
    public function search(string $modelName, $criteria, array $options = []): array
    {
        return $this
            ->getRecordManager()
            ->getRepository($modelName)
            ->search($criteria, $options['orders'] ?? null, $options['limit'] ?? null, $options['offset'] ?? null);
    }

    /**
     * Find ONE record by ID and options.
     *
     * @deprecated since version 7.0 and will be removed in 8.0, use the record manager instead.
     */
    public function find(string $modelName, int $id, array $options = []): ?array
    {
        return $this
            ->getRecordManager()
            ->getRepository($modelName)
            ->find($id, $options['fields'] ?? []);
    }

    /**
     * Find ONE record by criteria and options.
     *
     * @deprecated since version 7.0 and will be removed in 8.0, use the record manager instead.
     *
     * @param DomainInterface|array|null $criteria
     */
    public function findOneBy(string $modelName, $criteria = null, array $options = []): ?array
    {
        return $this
            ->getRecordManager()
            ->getRepository($modelName)
            ->findOneBy($criteria, $options['fields'] ?? [], $options['orders'] ?? null, $options['offset'] ?? null);
    }

    /**
     * Find all record(s) with options.
     *
     * @deprecated since version 7.0 and will be removed in 8.0, use the record manager instead.
     *
     * @return array<int, array>
     */
    public function findAll(string $modelName, array $options = []): array
    {
        return $this
            ->getRecordManager()
            ->getRepository($modelName)
            ->findAll($options['fields'] ?? [], $options['orders'] ?? null, $options['limit'] ?? null, $options['offset'] ?? null);
    }

    /**
     * Find record(s) by criteria and options.
     *
     * @deprecated since version 7.0 and will be removed in 8.0, use the record manager instead.
     *
     * @param DomainInterface|array|null $criteria
     *
     * @return array<int, array>
     */
    public function findBy(string $modelName, $criteria = null, array $options = []): array
    {
        return $this
            ->getRecordManager()
            ->getRepository($modelName)
            ->findBy($criteria, $options['fields'] ?? [], $options['orders'] ?? null, $options['limit'] ?? null, $options['offset'] ?? null);
    }

    /**
     * Check if a record exists.
     *
     * @deprecated since version 7.0 and will be removed in 8.0, use the record manager instead.
     */
    public function exists(string $modelName, int $id): bool
    {
        return $this
            ->getRecordManager()
            ->getRepository($modelName)
            ->exists($id);
    }

    /**
     * Count number of records for a model and criteria.
     *
     * @deprecated since version 7.0 and will be removed in 8.0, use the record manager instead.
     *
     * @param DomainInterface|array|null $criteria
     */
    public function count(string $modelName, $criteria = null): int
    {
        return $this
            ->getRecordManager()
            ->getRepository($modelName)
            ->count($criteria);
    }

    /**
     * Count all records for a model.
     *
     * @deprecated since version 7.0 and will be removed in 8.0, use the record manager instead.
     */
    public function countAll(string $modelName): int
    {
        return $this
            ->getRecordManager()
            ->getRepository($modelName)
            ->countAll();
    }

    /**
     * List model fields.
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
            'request_id' => uniqid('xmlrpc-request-', true),
            'name' => $name,
            'method' => $method,
            'parameters' => $parameters,
            'options' => $options,
        ];

        if ($this->logger) {
            $this->logger->debug('Calling method {name}::{method}', $loggerContext);
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
    private function initEndpoints(): void
    {
        $this->commonEndpoint = new Endpoint($this->url.'/'.self::ENDPOINT_COMMON);
        $this->objectEndpoint = new Endpoint($this->url.'/'.self::ENDPOINT_OBJECT);
    }
}
