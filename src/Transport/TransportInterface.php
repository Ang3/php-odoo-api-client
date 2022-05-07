<?php

namespace Ang3\Component\Odoo\Transport;

use Ang3\Component\Odoo\Exception\RequestException;

/**
 * @author Joanis ROUANET <https://github.com/Ang3>
 */
interface TransportInterface
{
    public const DEFAULT_TIMEOUT = 120;

    /**
     * Make a request to Odoo database.
     *
     * @throws RequestException when request failed
     */
    public function request(string $service, string $method, array $arguments = []): array;
}
