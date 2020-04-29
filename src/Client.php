<?php

namespace Ang3\Component\Odoo;

use Ang3\Component\Odoo\Exception\AuthenticationException;
use Ang3\Component\Odoo\Exception\MissingConfigParameterException;
use Ang3\Component\Odoo\Exception\RemoteException;
use Ang3\Component\Odoo\XmlRpc\Encoder;
use Ang3\Component\Odoo\XmlRpc\EncoderInterface;
use Ang3\Component\Odoo\XmlRpc\Endpoint;
use Psr\Log\LoggerInterface;

class Client
{
    /**
     * Endpoints.
     */
    public const ENDPOINT_COMMON = 'xmlrpc/2/common';
    public const ENDPOINT_OBJECT = 'xmlrpc/2/object';

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
     * @var LoggerInterface|null
     */
    private $logger;

    /**
     * @var int|null
     */
    private $uid;

    public function __construct(array $config, LoggerInterface $logger = null, EncoderInterface $encoder = null)
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
        $this->encoder = $encoder ?: new Encoder();
        $this->logger = $logger;
        $this->initEndpoints();
    }

    public function createManager(): Manager
    {
        return new Manager($this);
    }

    /**
     * @throws AuthenticationException when authentication failed
     * @throws RemoteException         when request failed
     *
     * @return mixed
     */
    public function call(string $name, string $method, array $parameters = [], array $options = [])
    {
        return $this->objectEndpoint->call('execute_kw', [
            $this->database,
            $this->getUid(),
            $this->password,
            $name,
            $method,
            $parameters,
            $this->encoder->encode($options, 'struct'),
        ]);
    }

    public function getVersion(): array
    {
        return $this->commonEndpoint->call('version');
    }

    /**
     * @throws AuthenticationException when authentication failed
     */
    public function getUid(): int
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
}
