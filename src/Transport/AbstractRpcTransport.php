<?php

namespace Ang3\Component\Odoo\Transport;

use Ang3\Component\Odoo\Exception\RemoteException;

/**
 * @author Joanis ROUANET <https://github.com/Ang3>
 */
abstract class AbstractRpcTransport implements TransportInterface
{
    public function normalizeRpcData(string $service, string $method, array $arguments = []): array
    {
        return [
            'jsonrpc' => '2.0',
            'method' => 'call',
            'params' => [
                'service' => $service,
                'method' => $method,
                'args' => $arguments,
            ],
            'id' => uniqid('odoo_jsonrpc'),
        ];
    }

    /**
     * @return mixed
     */
    protected function handleResult(array $payload)
    {
        if (is_array($payload['error'] ?? null)) {
            throw RemoteException::create($payload);
        }

        return $payload['result'];
    }
}
