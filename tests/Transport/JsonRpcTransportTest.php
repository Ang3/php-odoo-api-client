<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-api-client
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\Tests\Transport;

use Ang3\Component\Odoo\Connection;
use Ang3\Component\Odoo\Exception\RemoteException;
use Ang3\Component\Odoo\Exception\TransportException;
use Ang3\Component\Odoo\Transport\Client\JsonRpcHttpClientInterface;
use Ang3\Component\Odoo\Transport\JsonRpcTransport;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(JsonRpcTransport::class)]
final class JsonRpcTransportTest extends TestCase
{
    private Connection $connection;
    private string $baseUrl = 'http://example.com';

    protected function setUp(): void
    {
        // On mock la connexion
        $this->connection = $this->createMock(Connection::class);
        $this->connection->method('getUrl')->willReturn($this->baseUrl);
    }

    public function testRequestReturnsResult(): void
    {
        $httpClient = $this->createMock(JsonRpcHttpClientInterface::class);
        $httpClient->method('post')
            ->willReturn(json_encode(['result' => 'success']))
        ;

        $transport = new JsonRpcTransport($this->connection, $httpClient);

        $result = $transport->request('service', 'method');
        self::assertSame('success', $result);
    }

    public function testRequestThrowsTransportExceptionOnFalse(): void
    {
        $httpClient = $this->createMock(JsonRpcHttpClientInterface::class);
        $httpClient->method('post')->willReturn(false);

        $transport = new JsonRpcTransport($this->connection, $httpClient);

        $this->expectException(TransportException::class);
        $transport->request('service', 'method');
    }

    public function testRequestThrowsRemoteExceptionOnError(): void
    {
        $httpClient = $this->createMock(JsonRpcHttpClientInterface::class);
        $httpClient->method('post')
            ->willReturn(json_encode([
                'error' => [
                    'code' => 123,
                    'message' => 'Fail',
                    'data' => [
                        'debug' => 'Traceback (most recent call last):'."\n".
                            'File "/app/test.py", line 10, in <module>'."\n".
                            'print("Fail")',
                    ],
                ],
            ]))
        ;

        $transport = new JsonRpcTransport($this->connection, $httpClient);

        $this->expectException(RemoteException::class);
        $transport->request('service', 'method');
    }

    public function testRequestThrowsTransportExceptionOnInvalidJson(): void
    {
        $httpClient = $this->createMock(JsonRpcHttpClientInterface::class);
        $httpClient->method('post')->willReturn('{"invalidJson":'); // JSON invalide

        $transport = new JsonRpcTransport($this->connection, $httpClient);

        $this->expectException(TransportException::class);
        $transport->request('service', 'method');
    }
}
