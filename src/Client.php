<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-api-client
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo;

use Ang3\Component\Odoo\Enum\OdooRpcMethod;
use Ang3\Component\Odoo\Enum\OdooRpcService;
use Ang3\Component\Odoo\Exception\AuthenticationException;
use Ang3\Component\Odoo\Exception\ConnectionException;
use Ang3\Component\Odoo\Exception\RequestException;
use Ang3\Component\Odoo\Exception\TransportException;
use Ang3\Component\Odoo\Metadata\Version;
use Ang3\Component\Odoo\Transport\JsonRpcPhpStreamTransport;
use Ang3\Component\Odoo\Transport\TransportInterface;
use Psr\Log\LoggerInterface;

/**
 * @author Joanis ROUANET <https://github.com/Ang3>
 */
class Client
{
    private TransportInterface $transport;
    private ?int $uid = null;

    public function __construct(
        private readonly Connection $connection,
        TransportInterface $transport = null,
        private ?LoggerInterface $logger = null
    ) {
        $this->transport = $transport ?: new JsonRpcPhpStreamTransport($this->connection);
    }

    /**
     * Create a new client instance from a DSN.
     * DSN format: odoo://<user>:<password>@<host>/<database_name>.
     *
     * @static
     *
     * @throws ConnectionException on invalid DSN
     */
    public static function create(string $dsn, TransportInterface $transport = null, LoggerInterface $logger = null): self
    {
        return new self(Connection::parseDsn($dsn), $transport, $logger);
    }

    public function executeKw(string $name, string $method, array $parameters = [], array $options = []): mixed
    {
        return $this->request(
            OdooRpcService::Object->value,
            OdooRpcMethod::ExecuteKw->value,
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
        return Version::create((array) $this->request(OdooRpcService::Common->value, OdooRpcMethod::Version->value));
    }

    /**
     * @throws AuthenticationException when authentication failed
     */
    public function authenticate(): int
    {
        if (null === $this->uid) {
            $this->uid = (int) $this->request(
                OdooRpcService::Common->value,
                OdooRpcMethod::Login->value,
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
     * @throws RequestException   on request errors
     * @throws TransportException on transport errors
     */
    public function request(string $service, string $method, mixed ...$arguments): mixed
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
        $result = $this->transport->request($service, $method, $arguments);
        $runtime = microtime(true) - $runtime;

        $this->logger?->debug('Odoo request #{request_id} finished - Runtime: {runtime}s.', [
            'request_id' => $context['request_id'],
            'runtime' => number_format($runtime, 3, '.', ' '),
            'payload' => $result,
        ]);

        return $result;
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

    public function getLogger(): ?LoggerInterface
    {
        return $this->logger;
    }

    public function setLogger(?LoggerInterface $logger): self
    {
        $this->logger = $logger;

        return $this;
    }

    public function getUid(): ?int
    {
        return $this->uid;
    }
}
