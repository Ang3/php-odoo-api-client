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
use Webmozart\Assert\Assert;
use Webmozart\Assert\InvalidArgumentException;

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
     * @throws \InvalidArgumentException on invalid config
     */
    public static function createFromDsn(string $dsn): self
    {
        try {
            $dsn = DsnParser::parse($dsn);
        } catch (InvalidDsnException $e) {
            throw new \InvalidArgumentException('Invalid DSN', 0, $e);
        }

        $path = (string) $dsn->getPath();

        return self::createFromConfig([
            'host' => (string) $dsn->getHost(),
            'username' => (string) $dsn->getUser(),
            'password' => urldecode((string) $dsn->getPassword()),
            'database' => str_starts_with($path, '/') ? substr($path, 1) : $path,
            'scheme' => (string) $dsn->getScheme(),
        ]);
    }

    /**
     * @param array{host: string, username: string, password: string, database: string, scheme: string|null} $config
     *
     * @throws InvalidArgumentException on invalid configuration
     */
    public static function createFromConfig(array $config): self
    {
        $host = $config['host'] ?? null;
        Assert::stringNotEmpty($host, 'The host cannot be empty.');

        $username = $config['username'] ?? null;
        Assert::stringNotEmpty($username, 'The username cannot be empty.');

        $password = $config['password'] ?? null;
        Assert::stringNotEmpty($password, 'The password cannot be empty.');

        $database = $config['database'] ?? null;
        Assert::stringNotEmpty($database, 'The database cannot be empty.');

        $scheme = $config['scheme'] ?? 'https';
        Assert::inArray($scheme, ['http', 'https'], \sprintf('The DSN scheme "%s" is not supported (supported: "http" or "https").', $scheme));

        return new self($host, $username, $password, $database, $scheme);
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
