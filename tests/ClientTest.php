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
use Ang3\Component\Odoo\Enum\OdooMethod;
use Ang3\Component\Odoo\Enum\OdooService;
use Ang3\Component\Odoo\Exception\RemoteException;
use Ang3\Component\Odoo\Metadata\Version;
use Ang3\Component\Odoo\Transport\JsonRpcPhpStreamTransport;
use Ang3\Component\Odoo\Transport\TransportInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @coversDefaultClass \Ang3\Component\Odoo\Client
 *
 * @internal
 */
final class ClientTest extends TestCase
{
    private Client $client;
    private MockObject $connection;
    private MockObject $transport;
    private MockObject $logger;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->createMock(Connection::class);
        $this->transport = $this->createMock(TransportInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->client = new Client($this->connection, $this->transport, $this->logger);
    }

    /**
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $client = new Client($this->connection);

        // Asserting default transport
        $clientTransport = $client->getTransport();
        static::assertInstanceOf(JsonRpcPhpStreamTransport::class, $clientTransport);

        // Asserting optional logger
        $clientLogger = $client->getLogger();
        static::assertNull($clientLogger);
    }

    /**
     * @covers ::__construct
     */
    public function testConstructWithCustomTransport(): void
    {
        $customTransport = $this->createMock(TransportInterface::class);
        $client = new Client($this->connection, $customTransport);

        // Asserting custom transport
        $clientTransport = $client->getTransport();
        static::assertInstanceOf(TransportInterface::class, $clientTransport);
        static::assertSame($customTransport, $clientTransport);

        // Asserting optional logger
        static::assertNull($client->getLogger());
    }

    /**
     * @covers ::__construct
     */
    public function testConstructWithLogger(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $client = new Client($this->connection, null, $logger);

        // Asserting default transport
        $clientTransport = $client->getTransport();
        static::assertInstanceOf(JsonRpcPhpStreamTransport::class, $clientTransport);

        // Asserting logger
        $clientLogger = $client->getLogger();
        static::assertInstanceOf(LoggerInterface::class, $clientLogger);
        static::assertSame($logger, $clientLogger);
    }

    public static function provideRequestData(): array
    {
        return [
            [OdooService::Common, OdooMethod::Login],
            [OdooService::Common, OdooMethod::Version],
            [OdooService::Object, OdooMethod::ExecuteKw],
        ];
    }

    /**
     * @covers ::request
     *
     * @dataProvider provideRequestData
     */
    public function testRequest(OdooService $service, OdooMethod $method): void
    {
        $this->transport
            ->expects(static::once())
            ->method('request')
            ->with($service->value, $method->value, [1, 2, 3])
            ->willReturn('foo')
        ;

        $result = $this->client->request($service->value, $method->value, 1, 2, 3);
        static::assertSame('foo', $result);
    }

    /**
     * @covers ::request
     *
     * @dataProvider provideRequestData
     */
    public function testRequestRemoteError(OdooService $service, OdooMethod $method): void
    {
        self::expectException(RemoteException::class);
        $this->transport
            ->expects(static::once())
            ->method('request')
            ->with($service->value, $method->value, [1, 2, 3])
            ->willThrowException(RemoteException::create([
                'error' => [
                    'code' => 123,
                    'message' => 'Test error',
                    'data' => [
                        'debug' => 'foo',
                    ],
                ],
            ]))
        ;

        $this->client->request($service->value, $method->value, 1, 2, 3);
    }

    /**
     * @covers ::executeKw
     *
     * @depends testRequest
     */
    public function testExecuteKw(): void
    {
        [$database, $username, $password] = ['foo', 'bar', 'qux'];
        $this->connection->expects(static::exactly(2))->method('getDatabase')->willReturn($database);
        $this->connection->expects(static::once())->method('getUsername')->willReturn($username);
        $this->connection->expects(static::exactly(2))->method('getPassword')->willReturn($password);
        $expectedUid = 1337;
        $expectedResult = 'foo';

        $authenticationArguments = [OdooService::Common->value, OdooMethod::Login->value, [$database, $username, $password]];
        $requestArguments = [OdooService::Object->value, OdooMethod::ExecuteKw->value, [
            $database,
            $expectedUid,
            $password,
            $name = 'object_name',
            $method = 'object_method',
            $parameters = [1, 2, 3],
            $options = [4, 5, 6],
        ]];

        $this->transport
            ->expects(static::exactly(2))
            ->method('request')
            ->withConsecutive($authenticationArguments, $requestArguments)
            ->willReturn(static::returnCallback(function ($service) use ($expectedUid) {
                return match ($service) {
                    OdooService::Common->value => $expectedUid,
                    default => 'foo'
                };
            }))
        ;

        $result = $this->client->executeKw($name, $method, $parameters, $options);
        static::assertSame($expectedResult, $result);
    }

    /**
     * @covers ::version
     *
     * @depends testRequest
     */
    public function testVersion(): void
    {
        $this->transport
            ->expects(static::once())
            ->method('request')
            ->with(OdooService::Common->value, OdooMethod::Version->value)
            ->willReturn([
                'server_version_info' => [13, 3, 7, 'a', 'b', 'c'],
                'protocol_version' => 1,
            ])
        ;

        $version = $this->client->version();
        static::assertInstanceOf(Version::class, $version);
    }

    /**
     * @covers ::authenticate
     *
     * @depends testRequest
     */
    public function testAuthenticate(): void
    {
        [$database, $username, $password] = ['foo', 'bar', 'qux'];
        $this->connection->expects(static::once())->method('getDatabase')->willReturn($database);
        $this->connection->expects(static::once())->method('getUsername')->willReturn($username);
        $this->connection->expects(static::once())->method('getPassword')->willReturn($password);
        $expectedUid = 1337;

        $this->transport
            ->expects(static::once())
            ->method('request')
            ->with(OdooService::Common->value, OdooMethod::Login->value, [$database, $username, $password])
            ->willReturn($expectedUid)
        ;

        $uid = $this->client->authenticate();
        static::assertSame($expectedUid, $uid);
        static::assertSame($expectedUid, $this->client->getUid());
    }
}
