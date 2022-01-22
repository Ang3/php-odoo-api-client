<?php

namespace Ang3\Component\Odoo\Transport;

use Ang3\Component\Odoo\Exception\RequestException;

interface TransportInterface
{
    /**
     * Make a request to Odoo database.
     *
     * @throws RequestException when request failed
     */
    public function request(string $service, string $method, array $arguments = []): array;
}
