<?php

namespace Ang3\Component\Odoo\Transport;

use Ang3\Component\Odoo\Exception\RemoteException;

abstract class AbstractRpcTransport implements TransportInterface
{
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
