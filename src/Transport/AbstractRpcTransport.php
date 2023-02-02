<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-api-client
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

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

    protected function handleResult(array $payload): mixed
    {
        if (\is_array($payload['error'] ?? null)) {
            throw RemoteException::create($payload);
        }

        return $payload['result'];
    }
}
