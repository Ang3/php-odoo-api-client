<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-api-client
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\Tests;

use Ang3\Component\Odoo\Connection;
use Ang3\Component\Odoo\Exception\ConnectionException;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Ang3\Component\Odoo\Connection
 *
 * @internal
 */
final class ConnectionTest extends TestCase
{
    use FakerTrait;

    private Connection $connection;
    private string $host;
    private string $username;
    private string $password;
    private string $database;
    private string $dsn;

    protected function setUp(): void
    {
        parent::setUp();
        $this->host = 'my-company.odoo.com';
        $this->username = 'my-account@my-domain.com';
        $this->password = self::faker()->password(8);
        $this->database = 'my-database-3548373';
        $this->connection = new Connection($this->host, $this->username, $this->password, $this->database);
        $this->dsn = sprintf('odoo://%s:%s@%s/%s', $this->username, urlencode($this->password), $this->host, $this->database);
    }

    /**
     * @covers ::__toString
     */
    public function testToString(): void
    {
        static::assertSame($this->dsn, (string) $this->connection);
    }

    /**
     * @covers ::create
     *
     * @depends testGetters
     */
    public function testCreate(): void
    {
        $connection = Connection::create([
            'host' => $this->host,
            'username' => $this->username,
            'password' => $this->password,
            'database' => $this->database,
        ]);

        $this->testGetters($connection);
    }

    /**
     * @covers ::create
     *
     * @depends testGetters
     *
     * @testWith [null, "user", "pass", "database"]
     *           ["host", null, "pass", "database"]
     *           ["host", "user", null, "database"]
     *           ["host", "user", "pass", null]
     */
    public function testCreateWithMissingParameters(
        ?string $host = null,
        ?string $username = null,
        ?string $password = null,
        ?string $database = null
    ): void {
        $this->expectException(ConnectionException::class);

        Connection::create([
            'host' => $host,
            'username' => $username,
            'password' => $password,
            'database' => $database,
        ]);
    }

    /**
     * @covers ::parseDsn
     *
     * @depends testGetters
     */
    public function testParseDsn(): void
    {
        $connection = Connection::parseDsn($this->dsn);
        $this->testGetters($connection);
    }

    /**
     * @covers ::getDatabase
     * @covers ::getHost
     * @covers ::getPassword
     * @covers ::getUsername
     */
    public function testGetters(?Connection $connection = null): void
    {
        $connection = $connection ?: $this->connection;
        static::assertSame($this->host, $connection->getHost());
        static::assertSame($this->username, $connection->getUsername());
        static::assertSame($this->password, $connection->getPassword());
        static::assertSame($this->database, $connection->getDatabase());
    }

    /**
     * @covers ::getIdentifier
     */
    public function testGetIdentifier(): void
    {
        static::assertSame(sha1(sprintf('%s.%s.%s', $this->host, $this->database, $this->username)), $this->connection->getIdentifier());
    }
}
