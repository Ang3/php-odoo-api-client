<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-api-client
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\Tests;

use Ang3\Component\Odoo\Client;
use Ang3\Component\Odoo\Connection;
use Ang3\Component\Odoo\Enum\OdooRpcMethod;
use Ang3\Component\Odoo\Enum\OdooRpcService;
use Ang3\Component\Odoo\Exception\AuthenticationException;
use Ang3\Component\Odoo\Exception\RequestException;
use Ang3\Component\Odoo\Transport\TransportInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @internal
 *
 * @coversNothing
 */
final class ClientTest extends TestCase
{
    private Connection&MockObject $connection;
    private TransportInterface&MockObject $transport;

    protected function setUp(): void
    {
        $this->connection = $this->createMock(Connection::class);
        $this->transport = $this->createMock(TransportInterface::class);

        $this->connection->method('getDatabase')->willReturn('odoo_db');
        $this->connection->method('getUsername')->willReturn('admin');
        $this->connection->method('getPassword')->willReturn('secret');
    }

    public function testAuthenticateSuccess(): void
    {
        $this->transport
            ->expects(self::once())
            ->method('request')
            ->with(
                OdooRpcService::Common->value,
                OdooRpcMethod::Login->value,
                ['odoo_db', 'admin', 'secret']
            )
            ->willReturn(42)
        ;

        $client = new Client($this->connection, $this->transport);
        $uid = $client->authenticate();

        self::assertSame(42, $uid);
        self::assertSame(42, $client->getUid());
    }

    public function testAuthenticateFailureThrowsAuthenticationException(): void
    {
        $this->transport
            ->method('request')
            ->willReturn(null)
        ;

        $client = new Client($this->connection, $this->transport);
        $this->expectException(AuthenticationException::class);
        $client->authenticate();
    }

    public function testRequestWithoutAuthenticationThrowsRequestException(): void
    {
        $client = new Client($this->connection, $this->transport);

        $this->expectException(RequestException::class);
        $this->expectExceptionMessage('You must authenticate before making requests.');

        $client->request(
            OdooRpcService::Object->value,
            OdooRpcMethod::ExecuteKw->value,
            'odoo_db',
            1,
            'secret'
        );
    }

    public function testExecuteKwAuthenticatesAndExecutesRequest(): void
    {
        $this->transport
            ->expects(self::exactly(2))
            ->method('request')
            ->willReturnOnConsecutiveCalls(
                7,                  // authenticate()
                ['result' => true]  // execute_kw
            )
        ;

        $client = new Client($this->connection, $this->transport);
        $result = $client->executeKw(
            'res.partner',
            'search_read',
            [['is_company', '=', true]],
            ['limit' => 10]
        );

        self::assertSame(['result' => true], $result);
        self::assertSame(7, $client->getUid());
    }

    public function testRequestAfterAuthenticationUsesCachedUid(): void
    {
        $this->transport
            ->expects(self::exactly(2))
            ->method('request')
            ->willReturnOnConsecutiveCalls(
                5, // authenticate()
                'ok' // request()
            )
        ;

        $client = new Client($this->connection, $this->transport);
        $client->authenticate();
        $result = $client->request(
            OdooRpcService::Object->value,
            OdooRpcMethod::ExecuteKw->value,
            'odoo_db',
            5,
            'secret',
            'model',
            'method'
        );

        self::assertSame('ok', $result);
    }

    public function testWithTransportReturnsNewInstance(): void
    {
        $newTransport = $this->createMock(TransportInterface::class);

        $client = new Client($this->connection, $this->transport);
        $newClient = $client->withTransport($newTransport);

        self::assertNotSame($client, $newClient);
        self::assertSame($newTransport, $newClient->getTransport());
    }

    public function testWithLoggerReturnsNewInstanceAndLoggerIsUsed(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())
            ->method('info')
            ->with(
                self::stringContains('Odoo request'),
                self::arrayHasKey('service')
            )
        ;

        $transport = $this->createMock(TransportInterface::class);
        $transport->method('request')->willReturn(7);

        $client = new Client($this->connection, $transport, $logger);
        $client->authenticate();
    }
}
