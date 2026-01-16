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
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(Connection::class)]
final class ConnectionTest extends TestCase
{
    public function testGettersAndToString(): void
    {
        $connection = new Connection('odoo.local', 'admin', 'secret', 'test_db', 'https');

        self::assertSame('odoo.local', $connection->getHost());
        self::assertSame('admin', $connection->getUsername());
        self::assertSame('secret', $connection->getPassword());
        self::assertSame('test_db', $connection->getDatabase());
        self::assertSame('https', $connection->getScheme());
        self::assertSame('https://admin:secret@odoo.local/test_db', (string) $connection);
        self::assertSame('https://odoo.local', $connection->getUrl());
        self::assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $connection->getIdentifier());
    }

    public function testCreateFromConfig(): void
    {
        $config = [
            'host' => 'odoo.local',
            'username' => 'admin',
            'password' => 'secret',
            'database' => 'test_db',
        ];

        $connection = Connection::create($config);

        self::assertSame('odoo.local', $connection->getHost());
        self::assertSame('admin', $connection->getUsername());
        self::assertSame('secret', $connection->getPassword());
        self::assertSame('test_db', $connection->getDatabase());
        self::assertSame('https', $connection->getScheme());
    }

    public function testCreateThrowsExceptionOnMissingParam(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Connection::create([
            'host' => 'odoo.local',
            'username' => 'admin',
            // missing password
            'database' => 'test_db',
        ]);
    }

    public function testParseDsn(): void
    {
        $dsn = 'https://admin:secret@odoo.local/test_db';
        $connection = Connection::parseDsn($dsn);

        self::assertSame('odoo.local', $connection->getHost());
        self::assertSame('admin', $connection->getUsername());
        self::assertSame('secret', $connection->getPassword());
        self::assertSame('test_db', $connection->getDatabase());
        self::assertSame('https', $connection->getScheme());
    }

    public function testParseDsnWithMissingPartsThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Connection::parseDsn('https://@odoo.local/test_db');
    }

    public function testParseDsnWithUnsupportedSchemeThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Connection::parseDsn('ftp://admin:secret@odoo.local/test_db');
    }
}
