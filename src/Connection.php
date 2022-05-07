<?php

namespace Ang3\Component\Odoo;

use Ang3\Component\Odoo\Exception\MissingConfigParameterException;

/**
 * @author Joanis ROUANET <https://github.com/Ang3>
 */
class Connection
{
    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $database;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    public function __construct(string $url, string $database, string $username, string $password)
    {
        $this->url = $url;
        $this->database = $database;
        $this->username = $username;
        $this->password = $password;
    }

    public static function create(array $config): self
    {
        $getParam = static function ($config, $paramName) {
            $value = $config[$paramName] ?? null;

            if (null === $value) {
                throw new MissingConfigParameterException(sprintf('Missing config parameter name "%s".', $paramName));
            }

            return $value;
        };

        return new self(
            $getParam($config, 'url'),
            $getParam($config, 'database'),
            $getParam($config, 'username'),
            $getParam($config, 'password')
        );
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getDatabase(): string
    {
        return $this->database;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * Gets the unique name of this connection.
     */
    public function getIdentifier(): string
    {
        $database = preg_replace('([^a-zA-Z0-9_])', '_', $this->database);
        $user = preg_replace('([^a-zA-Z0-9_])', '_', $this->username);

        return sprintf('%s.%s.%s', sha1($this->url), $database, $user);
    }
}
