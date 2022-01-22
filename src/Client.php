<?php

namespace Ang3\Component\Odoo;

use Ang3\Component\Odoo\DBAL\Expression\ExpressionBuilderAwareTrait;
use Ang3\Component\Odoo\DBAL\Query\OrmQuery;
use Ang3\Component\Odoo\DBAL\RecordManager;
use Ang3\Component\Odoo\Exception\AuthenticationException;
use Ang3\Component\Odoo\Exception\MissingConfigParameterException;
use Ang3\Component\Odoo\Exception\RemoteException;
use Ang3\Component\Odoo\Exception\RequestException;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Client
{
    use ExpressionBuilderAwareTrait;

    /**
     * Services.
     */
    public const SERVICE_COMMON = 'common';
    public const SERVICE_OBJECT = 'object';

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
     * @var HttpClientInterface
     */
    private $httpClient;

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
        $this->httpClient = HttpClient::create([
            'base_uri' => "$url/jsonrpc",
        ]);
        $this->recordManager = new RecordManager($this);
        $this->logger = $logger;
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
     * Creates a new record and returns the new ID.
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

        $result = $this->execute($modelName, OrmQuery::CREATE, [[$data]]);

        return (int) array_shift($result);
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

        return (array) $this->execute($modelName, OrmQuery::READ, [$ids], $options);
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

        $ids = is_array($ids) ? $ids : [(int) $ids];
        $this->execute($modelName, OrmQuery::WRITE, [$ids, $data]);
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
        $this->execute($modelName, OrmQuery::UNLINK, [$ids]);
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

        return (array) $this->execute($modelName, OrmQuery::SEARCH, [$this->expr()->normalizeDomains($criteria)], $options);
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
        return (array) $this->execute($modelName, OrmQuery::SEARCH_READ, [$this->expr()->normalizeDomains($criteria)], $options);
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
        return $this->execute($modelName, OrmQuery::SEARCH_COUNT, [$this->expr()->normalizeDomains($criteria)]);
    }

    /**
     * List model fields.
     *
     * @deprecated since version 7.0 and will be removed in 8.0, use the record manager schema instead.
     */
    public function listFields(string $modelName, array $options = []): array
    {
        return (array) $this->execute($modelName, self::LIST_FIELDS, [], $options);
    }

    /**
     * @return mixed
     */
    public function execute(string $name, string $method, array $parameters = [], array $options = [])
    {
        return $this->request(self::SERVICE_OBJECT, 'execute_kw',
            $this->database,
            $this->authenticate(),
            $this->password,
            $name,
            $method,
            $parameters,
            $options
        );
    }

    public function version(): array
    {
        return $this->request(self::SERVICE_COMMON, 'version');
    }

    /**
     * @throws AuthenticationException when authentication failed
     */
    public function authenticate(): int
    {
        if (null === $this->uid) {
            $this->uid = $this->request(self::SERVICE_COMMON, 'login',
                $this->database,
                $this->username,
                $this->password
            );

            if (!$this->uid || !is_int($this->uid)) {
                throw new AuthenticationException();
            }
        }

        return $this->uid;
    }

    /**
     * @param mixed[] $arguments
     *
     * @return mixed
     */
    public function request(string $service, string $method, ...$arguments)
    {
        $context['request_id'] = uniqid('rpc', true);
        if ($this->logger) {
            $this->logger->info('JSON RPC request #{request_id} - {service}::{method} (uid: #{uid})', $context);
        }

        $data = [
            'jsonrpc' => '2.0',
            'method' => 'call',
            'params' => [
                'service' => $service,
                'method' => $method,
                'args' => $arguments,
            ],
            'id' => uniqid('odoo_request'),
        ];

        dump($data);

        $response = $this->httpClient->request('POST', '', [
            'json' => $data,
        ]);

        $result = $response->getContent();

        if ($this->logger) {
            $loggedResult = $result;
            $this->logger->debug(sprintf('Request result: %s', $loggedResult), [
                'request_id' => $context['request_id'],
            ]);
        }

        $payload = json_decode($result, true);

        if (is_array($payload['error'] ?? null)) {
            throw RemoteException::create($payload);
        }

        return $payload['result'];
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
}
