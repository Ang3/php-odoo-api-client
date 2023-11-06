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
use Ang3\Component\Odoo\Transport\JsonRpcPhpStreamTransport;
use Ang3\Component\Odoo\Transport\TransportInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Ang3\Component\Odoo\Transport\JsonRpcPhpStreamTransport
 *
 * @internal
 */
final class JsonRpcPhpStreamTransportTest extends TestCase
{
    public const TEST_URL = __DIR__.'/../Resources/jsonrpc_endpoints';
    public const SUCCESS_ENDPOINT = '/success';
    public const JSON_ERROR_ENDPOINT = '/json_error';
    public const REMOTE_ERROR_ENDPOINT = '/remote_error';

    private JsonRpcPhpStreamTransport $transport;
    private MockObject $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->createMock(Connection::class);
        $this->transport = new JsonRpcPhpStreamTransport($this->connection, TransportInterface::DEFAULT_TIMEOUT, '');
    }

    /**
     * @covers ::request
     */
    public function testRequest(): void
    {
        [$service, $method, $arguments] = ['foo', 'bar', [1, 2, 3]];
        $this->connection->expects(static::once())->method('getUrl')->willReturn(self::TEST_URL.self::SUCCESS_ENDPOINT);

        $result = $this->transport->request($service, $method, $arguments);
        static::assertSame(['success' => 'true'], $result);
    }

    /**
     * @covers ::request
     */
    public function testRequestDecodingError(): void
    {
        [$service, $method, $arguments] = ['foo', 'bar', [1, 2, 3]];
        $this->connection->expects(static::once())->method('getUrl')->willReturn(self::TEST_URL.self::JSON_ERROR_ENDPOINT);

        $this->expectException(TransportException::class);
        $this->transport->request($service, $method, $arguments);
    }

    /**
     * @covers ::request
     */
    public function testRequestRemoteError(): void
    {
        [$service, $method, $arguments] = ['foo', 'bar', [1, 2, 3]];
        $this->connection->expects(static::once())->method('getUrl')->willReturn(self::TEST_URL.self::REMOTE_ERROR_ENDPOINT);

        $this->expectException(RemoteException::class);
        $this->transport->request($service, $method, $arguments);
    }
}
