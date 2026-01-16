<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-api-client
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\Metadata;

/**
 * @author Joanis ROUANET <https://github.com/Ang3>
 */
readonly class Version
{
    public function __construct(
        private int $majorVersion,
        private int $minorVersion,
        private int $patchVersion,
        private string $buildName,
        private string $buildIdentifier,
        private string $buildVersion,
        private int $protocolVersion,
    ) {
    }

    /**
     * Creates the instance from Odoo response payload.
     *
     * @param mixed[] $payload
     */
    public static function create(array $payload): self
    {
        /** @var int|string $protocolVersion */
        $protocolVersion = $payload['protocol_version'];
        /** @var array{int, int, int, string, string, string} $infos */
        $infos = $payload['server_version_info'];

        return new self(
            (int) $infos[0],
            (int) $infos[1],
            (int) $infos[2],
            (string) $infos[3],
            (string) $infos[4],
            (string) $infos[5],
            (int) $protocolVersion
        );
    }

    public function __toString(): string
    {
        return $this->getName();
    }

    public function getName(): string
    {
        return \sprintf('%s.%s.%s+%s', $this->majorVersion, $this->minorVersion, $this->patchVersion, $this->buildName);
    }

    public function getMajorVersion(): int
    {
        return $this->majorVersion;
    }

    public function getMinorVersion(): int
    {
        return $this->minorVersion;
    }

    public function getPatchVersion(): int
    {
        return $this->patchVersion;
    }

    public function getBuildName(): string
    {
        return $this->buildName;
    }

    public function getBuildIdentifier(): string
    {
        return $this->buildIdentifier;
    }

    public function getBuildVersion(): string
    {
        return $this->buildVersion;
    }

    public function getProtocolVersion(): int
    {
        return $this->protocolVersion;
    }
}
