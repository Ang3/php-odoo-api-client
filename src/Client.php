<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-api-client
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo;

use Ang3\Component\Odoo\DBAL\Expression\ExpressionBuilder;
use Ang3\Component\Odoo\Enum\OdooMethod;
use Ang3\Component\Odoo\Enum\OdooService;
use Ang3\Component\Odoo\Exception\AuthenticationException;
use Ang3\Component\Odoo\Exception\MissingConfigParameterException;
use Ang3\Component\Odoo\Exception\RemoteException;
use Ang3\Component\Odoo\Exception\RequestException;
use Ang3\Component\Odoo\Metadata\Version;
use Ang3\Component\Odoo\Transport\JsonRpcPhpStreamTransport;
use Ang3\Component\Odoo\Transport\TransportException;
use Ang3\Component\Odoo\Transport\TransportInterface;
use Psr\Log\LoggerInterface;

/**
 * @author Joanis ROUANET <https://github.com/Ang3>
 */
class Client
{
    private TransportInterface $transport;
    private ExpressionBuilder $expressionBuilder;
    private ?int $uid = null;

    public function __construct(
        private readonly Connection $connection,
        ?TransportInterface $transport = null,
        private ?LoggerInterface $logger = null
    ) {
        $this->expressionBuilder = new ExpressionBuilder();
        $this->transport = $transport ?: new JsonRpcPhpStreamTransport($this->connection);
    }

    /**
     * Create a new client instance from array configuration.
     * The configuration array must have keys "url", "database", "username" and "password".
     *
     * @static
     *
     * @throws MissingConfigParameterException when a required parameter is missing
     */
    public static function create(
        array $config,
        ?TransportInterface $transport = null,
        ?LoggerInterface $logger = null
    ): self {
        return new self(Connection::create($config), $transport, $logger);
    }

    public function execute(string $name, string $method, array $parameters = [], array $options = []): mixed
    {
        return $this->request(
            OdooService::Object->value,
            OdooMethod::ExecuteKw->value,
            $this->connection->getDatabase(),
            $this->authenticate(),
            $this->connection->getPassword(),
            $name,
            $method,
            $parameters,
            $options
        );
    }

    public function version(): Version
    {
        return Version::create((array) $this->request(OdooService::Common->value, OdooMethod::Version->value));
    }

    /**
     * @throws AuthenticationException when authentication failed
     */
    public function authenticate(): int
    {
        if (null === $this->uid) {
            $this->uid = (int) $this->request(
                OdooService::Common->value,
                OdooMethod::Login->value,
                $this->connection->getDatabase(),
                $this->connection->getUsername(),
                $this->connection->getPassword()
            );

            if (!$this->uid) {
                throw new AuthenticationException();
            }
        }

        return $this->uid;
    }

    /**
     * @param mixed ...$arguments
     *
     * @throws RequestException   on request errors
     * @throws TransportException on transport errors
     */
    public function request(string $service, string $method, ...$arguments): mixed
    {
        $context = [
            'service' => $service,
            'method' => $method,
            'uid' => (int) $this->uid,
            'arguments' => \array_slice($arguments, 3),
            'request_id' => uniqid('rpc', true),
        ];

        $this->logger?->info('Odoo request #{request_id} - {service}::{method}({arguments}) (uid: #{uid})', $context);

        $runtime = microtime(true);
        $payload = $this->transport->request($service, $method, $arguments);
        $runtime = microtime(true) - $runtime;

        $this->logger?->debug('Odoo request #{request_id} finished - Runtime: {runtime}s.', [
            'request_id' => $context['request_id'],
            'runtime' => number_format($runtime, 3, '.', ' '),
            'payload' => $payload,
        ]);

        if (\is_array($payload['error'] ?? null)) {
            throw RemoteException::create($payload);
        }

        return $payload['result'];
    }

    public function getConnection(): Connection
    {
        return $this->connection;
    }

    public function getTransport(): TransportInterface
    {
        return $this->transport;
    }

    public function setTransport(TransportInterface $transport): self
    {
        if ($transport !== $this->transport) {
            $this->uid = null;
        }

        $this->transport = $transport;

        return $this;
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
