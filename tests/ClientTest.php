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
use Ang3\Component\Odoo\Metadata\Version;
use Ang3\Component\Odoo\Transport\TransportInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @internal
 */
#[CoversClass(Client::class)]
final class ClientTest extends TestCase
{
    private Connection $connection;
    private TransportInterface $transport;
    private LoggerInterface $logger;

    protected function setUp(): void
    {
        $this->connection = $this->createMock(Connection::class);
        $this->transport = $this->createMock(TransportInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
    }

    public function testAuthenticateReturnsUid(): void
    {
        $this->connection->method('getDatabase')->willReturn('test_db');
        $this->connection->method('getUsername')->willReturn('admin');
        $this->connection->method('getPassword')->willReturn('secret');

        $this->transport
            ->method('request')
            ->willReturnCallback(static function (string $service, string $method, mixed ...$args) {
                if ('common' === $service && 'login' === $method) {
                    return 42; // UID
                }

                return null;
            })
        ;

        $client = new Client($this->connection, $this->transport);
        self::assertSame(42, $client->authenticate());
    }

    public function testAuthenticateThrowsExceptionOnFailure(): void
    {
        $this->connection->method('getDatabase')->willReturn('test_db');
        $this->connection->method('getUsername')->willReturn('admin');
        $this->connection->method('getPassword')->willReturn('secret');

        // Mock transport to return null (failed login)
        $this->transport
            ->method('request')
            ->willReturn(null)
        ;

        $client = new Client($this->connection, $this->transport);

        $this->expectException(AuthenticationException::class);
        $client->authenticate();
    }

    public function testExecuteKwCallsTransportCorrectly(): void
    {
        $this->connection->method('getDatabase')->willReturn('test_db');
        $this->connection->method('getPassword')->willReturn('secret');

        $client = $this->getMockBuilder(Client::class)
            ->setConstructorArgs([$this->connection, $this->transport])
            ->onlyMethods(['authenticate'])
            ->getMock()
        ;

        $client->method('authenticate')->willReturn(42);

        $this->transport
            ->method('request')
            ->willReturnCallback(static function (string $service, string $method, mixed ...$args) {
                if ('object' === $service && 'execute_kw' === $method) {
                    return [1, 2, 3];
                }

                return null;
            })
        ;

        $result = $client->executeKw('res.partner', 'search', [['is_company' => true]]);
        self::assertSame([1, 2, 3], $result);
    }

    public function testVersionReturnsVersionObject(): void
    {
        $this->transport
            ->method('request')
            ->with(OdooRpcService::Common->value, OdooRpcMethod::Version->value)
            ->willReturn([
                'server_version_info' => [16, 0, 0, 'final', 'build_id', 'build_ver'],
                'protocol_version' => 1,
            ])
        ;

        $client = new Client($this->connection, $this->transport);
        $version = $client->version();

        self::assertInstanceOf(Version::class, $version);

        // Assert the getters
        self::assertSame(16, $version->getMajorVersion());
        self::assertSame(0, $version->getMinorVersion());
        self::assertSame(0, $version->getPatchVersion());
        self::assertSame('final', $version->getBuildName());
        self::assertSame('build_id', $version->getBuildIdentifier());
        self::assertSame('build_ver', $version->getBuildVersion());
        self::assertSame(1, $version->getProtocolVersion());
    }

    public function testWithLoggerReturnsNewInstance(): void
    {
        $client = new Client($this->connection, $this->transport);
        $newClient = $client->withLogger($this->logger);

        self::assertNotSame($client, $newClient);
        self::assertSame($this->logger, $newClient->getLogger());
    }
}
