<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-api-client
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\Tests\Transport\Client;

use Ang3\Component\Odoo\Transport\Client\JsonRpcHttpClient;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class JsonRpcHttpClientTest extends TestCase
{
    public function testPostReturnsResponse(): void
    {
        $client = $this->getMockBuilder(JsonRpcHttpClient::class)
            ->onlyMethods(['doRequest'])
            ->getMock()
        ;

        $url = 'https://example.com/jsonrpc';
        $payload = '{"jsonrpc":"2.0","method":"ping"}';
        $timeout = 5;

        $client->expects(self::once())
            ->method('doRequest')
            ->with($url, $payload, $timeout)
            ->willReturn('{"result":"pong"}')
        ;

        $result = $client->post($url, $payload, $timeout);

        self::assertSame('{"result":"pong"}', $result);
    }
}
