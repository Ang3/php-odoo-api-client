<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-api-client
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\Tests\Metadata;

use Ang3\Component\Odoo\Metadata\Version;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(Version::class)]
final class VersionTest extends TestCase
{
    public function testConstructorAndGetters(): void
    {
        $version = new Version(
            majorVersion: 15,
            minorVersion: 2,
            patchVersion: 3,
            buildName: 'nightly',
            buildIdentifier: 'abc123',
            buildVersion: '15.2.3+nightly',
            protocolVersion: 2
        );

        self::assertSame(15, $version->getMajorVersion());
        self::assertSame(2, $version->getMinorVersion());
        self::assertSame(3, $version->getPatchVersion());
        self::assertSame('nightly', $version->getBuildName());
        self::assertSame('abc123', $version->getBuildIdentifier());
        self::assertSame('15.2.3+nightly', $version->getBuildVersion());
        self::assertSame(2, $version->getProtocolVersion());
    }

    public function testCreateFromPayload(): void
    {
        $payload = [
            'protocol_version' => '3',
            'server_version_info' => [16, 1, 4, 'rc', 'xyz789', '16.1.4+rc'],
        ];

        $version = Version::create($payload);

        self::assertSame(16, $version->getMajorVersion());
        self::assertSame(1, $version->getMinorVersion());
        self::assertSame(4, $version->getPatchVersion());
        self::assertSame('rc', $version->getBuildName());
        self::assertSame('xyz789', $version->getBuildIdentifier());
        self::assertSame('16.1.4+rc', $version->getBuildVersion());
        self::assertSame(3, $version->getProtocolVersion());
    }

    public function testToStringReturnsName(): void
    {
        $version = new Version(
            majorVersion: 15,
            minorVersion: 2,
            patchVersion: 3,
            buildName: 'nightly',
            buildIdentifier: 'abc123',
            buildVersion: '15.2.3+nightly',
            protocolVersion: 2
        );

        self::assertSame('15.2.3+nightly', $version->__toString());
        self::assertSame('15.2.3+nightly', $version->getName());
    }
}
