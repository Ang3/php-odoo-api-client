<?php

namespace Ang3\Component\Odoo;

use Ang3\Component\Odoo\Exception\AuthenticationException;
use Ang3\Component\Odoo\Exception\MissingConfigParameterException;
use Ang3\Component\Odoo\Exception\RemoteException;
use Ang3\Component\Odoo\Exception\RequestException;
use Ang3\Component\Odoo\Expression\DomainInterface;
use Ang3\Component\Odoo\Expression\ExpressionBuilder;
use Ang3\Component\Odoo\XmlRpc\Encoder;
use Ang3\Component\Odoo\XmlRpc\EncoderInterface;
use Ang3\Component\Odoo\XmlRpc\Endpoint;
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
     * @var EncoderInterface
     */
    private $encoder;

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

    /**
     * @throws MissingConfigParameterException when a required parameter is missing
     */
    public function __construct(array $config, LoggerInterface $logger = null)
    {
        $getParam = static function ($config, $paramName, $paramKey) {
            $value = $config[$paramName] ?? $config[$paramKey] ?? null;

            if (null === $value) {
                throw new MissingConfigParameterException(sprintf('Missing config parameter name "%s" or parameter key %d', $paramName, $paramKey));
            }

            return $value;
        };

        $this->url = $getParam($config, 'url', 0);
        $this->database = $getParam($config, 'database', 1);
        $this->username = $getParam($config, 'username', 2);
        $this->password = $getParam($config, 'password', 3);
        $this->encoder = new Encoder();
        $this->expressionBuilder = new ExpressionBuilder();
        $this->logger = $logger;
        $this->initEndpoints();
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
        $options = $this->clearOptions($options, ['fields']);

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
        $result = (array) $this->findBy($modelName, $criteria, $options);

        return array_pop($result);
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
        try {
            return $this->objectEndpoint->call('execute_kw', [
                $this->database,
                $this->authenticate(),
                $this->password,
                $name,
                $method,
                $parameters,
                $this->encoder->encode($options, 'struct'),
            ]);
        } catch (RemoteException $e) {
            // Odoo raises an exception if the remote method does not return values.
            // This part allows to return null when it happens
            if (preg_match('#cannot marshal None unless allow_none is enabled#', $e->getMessage())) {
                return null;
            }

            throw $e;
        }
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
            $uid = $this->commonEndpoint
                ->call('authenticate', [
                    $this->database,
                    $this->username,
                    $this->password,
                    [],
                ]);

            if (!$uid || !is_int($uid)) {
                throw new AuthenticationException();
            }

            $this->uid = $uid;
        }

        return $this->uid;
    }

    public function expr(): ExpressionBuilder
    {
        return $this->expressionBuilder;
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

    public function setCommonEndpoint(Endpoint $commonEndpoint): self
    {
        $this->commonEndpoint = $commonEndpoint;

        return $this;
    }

    public function getObjectEndpoint(): Endpoint
    {
        return $this->objectEndpoint;
    }

    public function setObjectEndpoint(Endpoint $objectEndpoint): self
    {
        $this->objectEndpoint = $objectEndpoint;

        return $this;
    }

    public function getEncoder(): EncoderInterface
    {
        return $this->encoder;
    }

    public function setEncoder(EncoderInterface $encoder): self
    {
        $this->encoder = $encoder;
        $this->initEndpoints();

        return $this;
    }

    public function getExpressionBuilder(): ExpressionBuilder
    {
        return $this->expressionBuilder;
    }

    public function setExpressionBuilder(ExpressionBuilder $expressionBuilder): self
    {
        $this->expressionBuilder = $expressionBuilder;

        return $this;
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
    private function initEndpoints(): self
    {
        $this->commonEndpoint = new Endpoint(sprintf('%s/%s', $this->url, self::ENDPOINT_COMMON), $this->logger, $this->encoder);
        $this->objectEndpoint = new Endpoint(sprintf('%s/%s', $this->url, self::ENDPOINT_OBJECT), $this->logger, $this->encoder);

        return $this;
    }

    /**
     * @internal
     */
    private function clearOptions(array $options = [], array $toRemove = []): array
    {
        foreach ($options as $key => $value) {
            if (in_array($key, $toRemove, true)) {
                unset($options[$key]);
            }
        }

        return $options;
    }
}
