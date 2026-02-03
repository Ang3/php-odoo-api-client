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
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\InvalidArgumentException;

/**
 * @internal
 *
 * @coversNothing
 */
final class ConnectionTest extends TestCase
{
    public function testConstructorAndGetters(): void
    {
        $conn = new Connection('host', 'user', 'pass', 'db', 'http');

        self::assertSame('host', $conn->getHost());
        self::assertSame('user', $conn->getUsername());
        self::assertSame('pass', $conn->getPassword());
        self::assertSame('db', $conn->getDatabase());
        self::assertSame('http', $conn->getScheme());

        self::assertStringContainsString('http://user:pass@host/db', (string) $conn);
        self::assertStringContainsString('http://host', $conn->getUrl());
        self::assertNotEmpty($conn->getIdentifier());
    }

    public function testCreateFromConfigSuccess(): void
    {
        $config = [
            'host' => 'myhost',
            'username' => 'myuser',
            'password' => 'mypass',
            'database' => 'mydb',
            'scheme' => 'https',
        ];

        $conn = Connection::createFromConfig($config);

        self::assertSame('myhost', $conn->getHost());
        self::assertSame('myuser', $conn->getUsername());
        self::assertSame('mypass', $conn->getPassword());
        self::assertSame('mydb', $conn->getDatabase());
        self::assertSame('https', $conn->getScheme());
    }

    public function testCreateFromConfigDefaultsSchemeToHttps(): void
    {
        $config = [
            'host' => 'h',
            'username' => 'u',
            'password' => 'p',
            'database' => 'd',
        ];

        $conn = Connection::createFromConfig($config);

        self::assertSame('https', $conn->getScheme());
    }

    public function testCreateFromConfigInvalidConfigThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Connection::createFromConfig([]);
    }

    public function testCreateFromConfigUnsupportedSchemeThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Connection::createFromConfig([
            'host' => 'h',
            'username' => 'u',
            'password' => 'p',
            'database' => 'd',
            'scheme' => 'ftp',
        ]);
    }

    public function testCreateFromDsnSuccess(): void
    {
        $dsn = 'https://user:pass@host/db';
        $conn = Connection::createFromDsn($dsn);

        self::assertSame('host', $conn->getHost());
        self::assertSame('user', $conn->getUsername());
        self::assertSame('pass', $conn->getPassword());
        self::assertSame('db', $conn->getDatabase());
        self::assertSame('https', $conn->getScheme());
    }

    public function testCreateFromDsnInvalidThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Connection::createFromDsn('invalid-dsn');
    }

    public function testToStringAndGetUrl(): void
    {
        $conn = new Connection('host', 'user', 'pass', 'db', 'https');

        $str = (string) $conn;
        self::assertStringContainsString('https://user:pass@host/db', $str);
        self::assertSame('https://host', $conn->getUrl());
    }

    public function testGetIdentifierIsStable(): void
    {
        $conn1 = new Connection('host', 'user', 'pass', 'db');
        $conn2 = new Connection('host', 'user', 'pass', 'db');

        self::assertSame($conn1->getIdentifier(), $conn2->getIdentifier());
    }
}
