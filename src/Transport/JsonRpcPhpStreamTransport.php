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
use Ang3\Component\Odoo\Exception\RequestException;

/**
 * @author Joanis ROUANET <https://github.com/Ang3>
 * @author Jules Sayer <https://github.com/Wilders>
 */
class JsonRpcPhpStreamTransport extends AbstractRpcTransport
{
    /**
     * JSON-RPC endpoint.
     */
    public const ENDPOINT_JSON_RPC = 'jsonrpc';

    public function __construct(
        private readonly Connection $connection,
        private readonly int $timeOut = TransportInterface::DEFAULT_TIMEOUT
    )
    {
    }

    public function request(string $service, string $method, array $arguments = []): array
    {
        $payload = json_encode($this->normalizeRpcData($service, $method, $arguments));

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new RequestException(sprintf('Failed to encode data to JSON: %s', json_last_error_msg()));
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'timeout' => $this->timeOut,
                'header' => 'Content-Type: application/json',
                'content' => $payload,
            ],
        ]);

        $url = sprintf('%s/%s', $this->connection->getUrl(), self::ENDPOINT_JSON_RPC);
        $request = file_get_contents($url, false, $context);

        if (false === $request) {
            throw new RequestException('Unable to connect to Odoo.');
        }

        $data = json_decode($request, true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new RequestException(sprintf('Failed to decode JSON data: %s', json_last_error_msg()));
        }

        return (array) $data;
    }
}
