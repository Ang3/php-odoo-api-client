<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-api-client
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo;

use Nyholm\Dsn\DsnParser;
use Nyholm\Dsn\Exception\InvalidDsnException;

/**
 * @author Joanis ROUANET <https://github.com/Ang3>
 */
readonly class Connection
{
    public function __construct(
        private string $host,
        private string $username,
        private string $password,
        private string $database,
        private string $scheme = 'https',
    ) {
    }

    public function __toString(): string
    {
        return \sprintf('%s://%s:%s@%s/%s', $this->scheme, $this->username, urlencode($this->password), $this->host, $this->database);
    }

    /**
     * @param array<string, string> $config
     *
     * @throws \InvalidArgumentException on invalid config
     */
    public static function create(array $config): self
    {
        $getParam = static function (string $paramName) use ($config): string {
            $value = $config[$paramName] ?? null;

            if (null === $value) {
                throw new \InvalidArgumentException(\sprintf('Missing configuration parameter "%s".', $paramName));
            }

            if (!\is_string($value)) {
                throw new \InvalidArgumentException(\sprintf('The parameter "%s" should be a string, got "%s".', $paramName, \gettype($value)));
            }

            return $value;
        };

        return new self(
            $getParam('host'),
            $getParam('username'),
            $getParam('password'),
            $getParam('database'),
            $config['scheme'] ?? 'https',
        );
    }

    /**
     * @throws \InvalidArgumentException on invalid config
     */
    public static function parseDsn(string $dsn): self
    {
        try {
            $dsn = DsnParser::parse($dsn);
        } catch (InvalidDsnException $e) {
            throw new \InvalidArgumentException('Invalid DSN', 0, $e);
        }

        [$scheme, $host, $user, $password, $path] = [
            $dsn->getScheme(),
            $dsn->getHost(),
            $dsn->getUser(),
            $dsn->getPassword(),
            $dsn->getPath(),
        ];

        if (!$scheme) {
            throw new \InvalidArgumentException('Missing DSN scheme.');
        }

        if (!\in_array($scheme, ['http', 'https'], true)) {
            throw new \InvalidArgumentException(\sprintf('The DSN scheme "%s" is not supported (supported: "http" or "https").', $scheme));
        }

        if (!$host) {
            throw new \InvalidArgumentException('Missing DSN host.');
        }

        if (!$user) {
            throw new \InvalidArgumentException('Missing DSN username.');
        }

        if (!$password) {
            throw new \InvalidArgumentException('Missing DSN user password.');
        }

        if (!$path) {
            throw new \InvalidArgumentException('Missing DSN path.');
        }

        $database = str_starts_with($path, '/') ? substr($path, 1) : $path;

        return new self($host, $user, urldecode($password), $database);
    }

    /**
     * Gets the unique name of this connection.
     */
    public function getIdentifier(): string
    {
        return sha1(\sprintf('%s.%s.%s', $this->host, $this->database, $this->username));
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getDatabase(): string
    {
        return $this->database;
    }

    public function getScheme(): string
    {
        return $this->scheme;
    }

    public function getUrl(): string
    {
        return \sprintf('%s://%s', $this->scheme, $this->host);
    }
}
