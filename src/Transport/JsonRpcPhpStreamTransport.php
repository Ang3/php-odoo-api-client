<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-api-client
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\Transport;

use Ang3\Component\Odoo\Connection;
use Ang3\Component\Odoo\Exception\RemoteException;
use Ang3\Component\Odoo\Exception\TransportException;

/**
 * @author Joanis ROUANET <https://github.com/Ang3>
 * @author Jules Sayer <https://github.com/Wilders>
 */
class JsonRpcPhpStreamTransport implements TransportInterface
{
    /**
     * JSON-RPC endpoint.
     */
    public const DEFAULT_ENDPOINT = '/jsonrpc';

    public function __construct(
        private readonly Connection $connection,
        private readonly int $timeOut = TransportInterface::DEFAULT_TIMEOUT
    ) {}

    public function request(string $service, string $method, array $arguments = []): mixed
    {
        $payload = json_encode([
            'jsonrpc' => '2.0',
            'method' => 'call',
            'params' => [
                'service' => $service,
                'method' => $method,
                'args' => $arguments,
            ],
            'id' => uniqid('odoo_jsonrpc'),
        ]);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new TransportException(sprintf('Failed to encode data to JSON: %s', json_last_error_msg()));
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'timeout' => $this->timeOut,
                'header' => 'Content-Type: application/json',
                'content' => $payload,
            ],
        ]);

        $endpointUrl = $this->connection->getUrl().self::DEFAULT_ENDPOINT;
        $request = file_get_contents($endpointUrl, false, $context);

        if (false === $request) {
            throw new TransportException('JSON RPC request failed - Unable to get stream contents.');
        }

        $data = (array) json_decode($request, true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new TransportException(sprintf('Failed to decode JSON data: %s', json_last_error_msg()));
        }

        if (\is_array($data['error'] ?? null)) {
            throw RemoteException::create($data);
        }

        return $data['result'] ?? null;
    }

    public function getConnection(): Connection
    {
        return $this->connection;
    }

    public function getTimeOut(): int
    {
        return $this->timeOut;
    }
}
