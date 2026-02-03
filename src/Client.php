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
use Ang3\Component\Odoo\Exception\RequestException;
use Ang3\Component\Odoo\Exception\TransportException;
use Ang3\Component\Odoo\Metadata\Version;
use Ang3\Component\Odoo\Transport\JsonRpcTransport;
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
        ?TransportInterface $transport = null,
        private readonly ?LoggerInterface $logger = null,
    ) {
        $this->transport = $transport ?: new JsonRpcTransport($this->connection);
    }

    /**
     * @param mixed[] $parameters
     * @param mixed[] $options
     */
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
     * @throws AuthenticationException on authentication error
     */
    public function authenticate(): int
    {
        /** @var int|null $uid */
        $uid = $this->request(
            OdooRpcService::Common->value,
            OdooRpcMethod::Login->value,
            $this->connection->getDatabase(),
            $this->connection->getUsername(),
            $this->connection->getPassword()
        );

        if (!$uid) {
            throw new AuthenticationException();
        }

        $this->uid = $uid;

        return $this->uid;
    }

    /**
     * @throws RequestException   on request errors
     * @throws TransportException on transport errors
     */
    public function request(string $service, string $method, mixed ...$arguments): mixed
    {
        $uid = $this->uid;

        if ($service !== OdooRpcService::Common->value || $method !== OdooRpcMethod::Login->value) {
            $uid ?: throw new RequestException('You must authenticate before making requests.');
        }

        $context = [
            'service' => $service,
            'method' => $method,
            'uid' => $uid,
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

    public function withTransport(TransportInterface $transport): self
    {
        return new self($this->connection, $transport, $this->logger);
    }

    public function getLogger(): ?LoggerInterface
    {
        return $this->logger;
    }

    public function withLogger(LoggerInterface $logger): self
    {
        return new self($this->connection, $this->transport, $logger);
    }

    public function getUid(): ?int
    {
        return $this->uid;
    }
}
