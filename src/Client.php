<?php

namespace Ang3\Component\Odoo;

use Ang3\Component\Odoo\Exception\AuthenticationException;
use Ang3\Component\Odoo\Exception\MissingConfigParameterException;
use Ang3\Component\Odoo\Exception\RemoteException;
use Ang3\Component\Odoo\Exception\RequestException;
use Ang3\Component\Odoo\Expression\ExpressionBuilder;
use Ang3\Component\Odoo\Transport\JsonRpcPhpNativeStreamTransport;
use Ang3\Component\Odoo\Transport\TransportInterface;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;

class Client
{
    /**
     * Services.
     */
    public const SERVICE_COMMON = 'common';
    public const SERVICE_OBJECT = 'object';

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
     * Special commands.
     */
    public const LIST_FIELDS = 'fields_get';

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var TransportInterface
     */
    private $transport;

    /**
     * @var ExpressionBuilder
     */
    private $expressionBuilder;

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

    public function __construct(Connection $connection, TransportInterface $transport = null, LoggerInterface $logger = null)
    {
        $this->connection = $connection;
        $this->expressionBuilder = new ExpressionBuilder();
        $this->transport = $transport ?: new JsonRpcPhpNativeStreamTransport($this->connection);
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
    public static function create(array $config, TransportInterface $transport = null, LoggerInterface $logger = null): self
    {
        return new self(Connection::create($config), $transport, $logger);
    }

    /**
     * Creates a new record and returns the new ID.
     *
     * @throws InvalidArgumentException when $data is empty
     * @throws RequestException         when request failed
     *
     * @return int the ID of the new record
     */
    public function insert(string $modelName, array $data): int
    {
        if (!$data) {
            throw new InvalidArgumentException('Data cannot be empty');
        }

        $result = $this->execute($modelName, self::CREATE, [[$data]]);

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
        $ids = is_int($ids) ? [$ids] : (array) $ids;

        return (array) $this->execute($modelName, self::READ, [$ids], $options);
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
        $this->execute($modelName, self::WRITE, [$ids, $data]);
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
        $this->execute($modelName, self::UNLINK, [$ids]);
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

        return (array) $this->execute($modelName, self::SEARCH, [$this->expressionBuilder->normalizeDomains($criteria)], $options);
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
        return (array) $this->execute($modelName, self::SEARCH_READ, [$this->expressionBuilder->normalizeDomains($criteria)], $options);
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
        return $this->execute($modelName, self::SEARCH_COUNT, [$this->expressionBuilder->normalizeDomains($criteria)]);
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
        return $this->request(
            self::SERVICE_OBJECT,
            'execute_kw',
            $this->connection->getDatabase(),
            $this->authenticate(),
            $this->connection->getPassword(),
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
            $this->uid = $this->request(
                self::SERVICE_COMMON,
                'login',
                $this->connection->getDatabase(),
                $this->connection->getUsername(),
                $this->connection->getPassword()
            );

            if (!$this->uid || !is_int($this->uid)) {
                throw new AuthenticationException();
            }
        }

        return $this->uid;
    }

    /**
     * @param mixed ...$arguments
     *
     * @return mixed
     */
    public function request(string $service, string $method, ...$arguments)
    {
        $context['request_id'] = uniqid('rpc', true);
        if ($this->logger) {
            $this->logger->info('JSON RPC request #{request_id} - {service}::{method} (uid: #{uid})', $context);
        }

        $payload = $this->transport->request($service, $method, $arguments);

        if ($this->logger) {
            $loggedResult = json_encode($payload);
            $this->logger->debug(sprintf('Request result: %s', $loggedResult), [
                'request_id' => $context['request_id'],
            ]);
        }

        if (is_array($payload['error'] ?? null)) {
            throw RemoteException::create($payload);
        }

        return $payload['result'];
    }

    public function getConnection(): Connection
    {
        return $this->connection;
    }

    public function setConnection(Connection $connection): void
    {
        $this->connection = $connection;
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

        return $this;
    }
}
