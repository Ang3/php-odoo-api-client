<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-api-client
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\Transport\Client;

class JsonRpcHttpClient implements JsonRpcHttpClientInterface
{
    public function post(string $url, string $payload, int $timeout): string|false
    {
        return $this->doRequest($url, $payload, $timeout);
    }

    /**
     * Extracted for testing / mocking.
     *
     * @codeCoverageIgnore
     */
    protected function doRequest(string $url, string $payload, int $timeout): string|false
    {
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'timeout' => $timeout,
                'header' => 'Content-Type: application/json',
                'content' => $payload,
            ],
        ]);

        return file_get_contents($url, false, $context);
    }
}
