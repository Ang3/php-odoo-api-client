<?php

namespace Ang3\Component\Odoo\Transport;

use Ang3\Component\Odoo\Connection;
use Ang3\Component\Odoo\Exception\RequestException;

/**
 * @author Joanis ROUANET <https://github.com/Ang3>
 * @author Jules Sayer <https://github.com/Wilders>
 */
class JsonRpcPhpNativeStreamTransport extends AbstractRpcTransport
{
    /**
     * JSON-RPC endpoint.
     */
    public const ENDPOINT_JSON_RPC = 'jsonrpc';

    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $credentials)
    {
        $this->connection = $credentials;
    }

    public function request(string $service, string $method, array $arguments = []): array
    {
        $payload = json_encode([
            'jsonrpc' => '2.0',
            'method' => 'call',
            'params' => [
                'service' => $service,
                'method' => $method,
                'args' => $arguments,
            ],
            'id' => uniqid('odoo_request'),
        ]);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new RequestException(sprintf('Failed to encode data to JSON: %s', json_last_error_msg()));
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'timeout' => 120,
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

        return $data;
    }
}
