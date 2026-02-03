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
use Ang3\Component\Odoo\Transport\Client\JsonRpcHttpClient;
use Ang3\Component\Odoo\Transport\Client\JsonRpcHttpClientInterface;

/**
 * @author Joanis ROUANET <https://github.com/Ang3>
 * @author Jules Sayer <https://github.com/Wilders>
 */
class JsonRpcTransport implements TransportInterface
{
    public const DEFAULT_ENDPOINT = '/jsonrpc';

    private JsonRpcHttpClientInterface $httpClient;

    public function __construct(
        private readonly Connection $connection,
        ?JsonRpcHttpClientInterface $httpClient = null,
        private readonly int $timeOut = TransportInterface::DEFAULT_TIMEOUT,
    ) {
        $this->httpClient = $httpClient ?: new JsonRpcHttpClient();
    }

    /**
     * @param mixed[] $arguments
     */
    public function request(string $service, string $method, array $arguments = []): mixed
    {
        $payload = (string) json_encode([
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
            throw new TransportException(\sprintf('Failed to encode data to JSON: %s', json_last_error_msg()));
        }

        $endpointUrl = $this->connection->getUrl().self::DEFAULT_ENDPOINT;
        $response = $this->httpClient->post($endpointUrl, $payload, $this->timeOut);

        if (false === $response) {
            throw new TransportException('JSON RPC request failed - Unable to get response.');
        }

        $data = (array) json_decode($response, true);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new TransportException(\sprintf('Failed to decode JSON data: %s', json_last_error_msg()));
        }

        if (\is_array($data['error'] ?? null)) {
            throw RemoteException::create($data);
        }

        return $data['result'] ?? null;
    }
}
