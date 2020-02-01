<?php

namespace Ang3\Component\Odoo;

use Ang3\Component\Odoo\Exception\RequestException;
use InvalidArgumentException;
use Ripcord\Client\Client;
use Ripcord\Ripcord;

/**
 * @author Joanis ROUANET
 */
class ExternalApiClient
{
    /**
     * System username.
     */
    const SYSTEM_USER = '__system__';

    /**
     * Endpoints.
     */
    const ENDPOINT_COMMON = 'xmlrpc/2/common';
    const ENDPOINT_OBJECT = 'xmlrpc/2/object';

    /**
     * Object default methods.
     */
    const CREATE = 'create';
    const READ = 'read';
    const WRITE = 'write';
    const DELETE = 'unlink';
    const SEARCH = 'search';
    const SEARCH_COUNT = 'search_count';
    const SEARCH_READ = 'search_read';
    const LIST_FIELDS = 'fields_get';

    /**
     * API endpoints list.
     *
     * @static
     *
     * @var array
     */
    private static $endpoints = [
        self::ENDPOINT_COMMON,
        self::ENDPOINT_OBJECT,
    ];

    /**
     * API object methods list.
     *
     * @static
     *
     * @var array
     */
    private static $defaultMethods = [
        self::CREATE,
        self::READ,
        self::WRITE,
        self::DELETE,
        self::SEARCH,
        self::SEARCH_COUNT,
        self::SEARCH_READ,
        self::LIST_FIELDS,
    ];

    /**
     * Client host.
     *
     * @var string
     */
    private $host;

    /**
     * Odoo database name.
     *
     * @var string
     */
    private $database;

    /**
     * Odoo user.
     *
     * @var string
     */
    private $user;

    /**
     * Odoo user password.
     *
     * @var string
     */
    private $password;

    /**
     * Client options.
     *
     * @var array
     */
    private $options;

    /**
     * Clients instances.
     *
     * @var array
     */
    private $clients = [];

    /**
     * Client uid.
     *
     * @var string
     */
    private $uid;

    public function __construct(string $host, string $database, string $user, string $password, array $options = [])
    {
        $this->host = $host;
        $this->database = $database;
        $this->user = $user;
        $this->password = $password;
        $this->options = $options;
    }

    /**
     * @static
     *
     * @throws InvalidArgumentException when a parameters is missing or not valid
     */
    public static function createfromArray(array $parameters = [], array $options = []): self
    {
        // Si pas d'URL
        if (!array_key_exists('url', $parameters)) {
            throw new InvalidArgumentException('Missing required parameter "url".');
        }

        // Si pas de nom de base de données Odoo
        if (!array_key_exists('database', $parameters)) {
            throw new InvalidArgumentException('Missing required parameter "database".');
        }

        // Si pas d'utilisateur
        if (!array_key_exists('user', $parameters)) {
            throw new InvalidArgumentException('Missing required parameter "user".');
        }

        // Si pas de mot de passe
        if (!array_key_exists('password', $parameters)) {
            throw new InvalidArgumentException('Missing required parameter "password".');
        }

        // Récupération des options éventuelles du client
        $options = array_key_exists('options', $parameters) ? $parameters['options'] : [];

        // Retour de la nouvelle instance
        return new self($parameters['url'], $parameters['database'], $parameters['user'], $parameters['password'], $options);
    }

    /**
     * Get used API version.
     */
    public function version(): string
    {
        return $this
            ->getXmlRpcClient(self::ENDPOINT_COMMON)
            ->version()
        ;
    }

    /**
     * Create a record.
     */
    public function create(string $modelName, array $fields = []): int
    {
        return $this->call($modelName, self::CREATE, [$fields]);
    }

    /**
     * Read models.
     *
     * @param array|int $ids
     */
    public function read(string $modelName, $ids, array $options = []): array
    {
        return $this->call($modelName, self::READ, (array) $ids, $options);
    }

    /**
     * Update a record.
     *
     * @param array|int $ids
     */
    public function update(string $modelName, $ids, array $fields = []): array
    {
        return $this->call($modelName, self::WRITE, [(array) $ids, $fields]);
    }

    /**
     * Delete models.
     *
     * @param array|int $ids
     */
    public function delete(string $modelName, $ids): array
    {
        return $this->call($modelName, self::DELETE, [(array) $ids]);
    }

    /**
     * List model fields.
     */
    public function listFields(string $modelName, array $options = []): array
    {
        return $this->call($modelName, self::LIST_FIELDS, [], $options);
    }

    /**
     * Search and read models.
     */
    public function searchAndRead(string $modelName, array $parameters = [], array $options = []): array
    {
        return $this->call($modelName, self::SEARCH_READ, [$parameters], $options);
    }

    /**
     * Search models.
     */
    public function search(string $modelName, array $parameters = [], array $options = []): array
    {
        return $this->call($modelName, self::SEARCH, [$parameters], $options);
    }

    /**
     * Count models.
     */
    public function count(string $modelName, array $parameters = [], array $options = []): int
    {
        return $this->call($modelName, self::SEARCH_COUNT, [$parameters], $options);
    }

    /**
     * Call method of Odoo model.
     *
     * @param string $name       The model name
     * @param string $method     The method name
     * @param array  $parameters An array/list of parameters passed by position
     * @param array  $options    A mapping/dict of parameters to pass by keyword (optional)
     *
     * @throws RequestException when the request failed
     *
     * @return mixed
     */
    public function call(string $name, string $method, array $parameters = [], array $options = [])
    {
        // Lancement de la requête et récupération des données
        $data = $this
            ->getXmlRpcClient(self::ENDPOINT_OBJECT)
            ->execute_kw($this->database, $this->getUid(), $this->password, $name, $method, $parameters, $options)
        ;

        // Si les données représente une erreur
        if (is_array($data) && xmlrpc_is_fault($data)) {
            throw new RequestException($this, $name, $method, $parameters, $options, $data);
        }

        // Retour des données
        return $data;
    }

    /**
     * Get the uid of logged client.
     */
    public function getUid(bool $forceAuthentication = false): int
    {
        // Si on souhaite se connecter en tant que super-utilisateur
        if (self::SYSTEM_USER === $this->user) {
            // Retour du premier utilisateur (OdooBot)
            return 1;
        }

        // Si on force l'authentification ou que l'on a pas d'ID utilisateur
        if ($forceAuthentication || null === $this->uid) {
            // Authentification
            $this->uid = $this
                ->getXmlRpcClient(self::ENDPOINT_COMMON)
                ->authenticate(
                    $this->database,
                    $this->user,
                    $this->password,
                    []
                );
        }

        // Retour de l'ID
        return $this->uid;
    }

    /**
     * Get XmlRpc Client.
     *
     * This method returns an XmlRpc Client for the requested endpoint.
     *
     * @param string $endpoint The API endpoint
     * @param bool   $refresh  Force to create a new client
     *
     * @throws InvalidArgumentException when the endpoint is not valid
     */
    public function getXmlRpcClient(string $endpoint = self::ENDPOINT_COMMON, bool $refresh = false): Client
    {
        // Si le point de terminaison n'est pas valide
        if (!$this->checkEndpoint($endpoint)) {
            throw new InvalidArgumentException(sprintf('The endpoint "%s" is not valid.', $endpoint));
        }

        // Si on demande le rafraichissement du client ou que l'on ne possède pas le client en cache
        if ($refresh || !array_key_exists($endpoint, $this->clients)) {
            // Création d'un nouveau client pour le point de terminaison
            $this->clients[$endpoint] = Ripcord::client($this->host.'/'.$endpoint);
        }

        // Retour du client selon le point de terminaison
        return $this->clients[$endpoint];
    }

    /**
     * Check if the given endpoint is valid.
     */
    public function checkEndpoint(string $endpoint): bool
    {
        return in_array($endpoint, self::$endpoints);
    }

    public function setHost(string $host): self
    {
        $this->host = $host;

        return $this;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function setDatabase(string $database): self
    {
        $this->database = $database;

        return $this;
    }

    public function getDatabase(): string
    {
        return $this->database;
    }

    public function setUser(string $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getUser(): string
    {
        return $this->user;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setOptions(array $options): self
    {
        $this->options = $options;

        return $this;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Set clients.
     *
     * @throws InvalidArgumentException when at least one client is not valid
     */
    public function setClients(array $clients): self
    {
        // Pour chaque client
        foreach ($clients as $client) {
            // Si ce n'est pas un objet
            if (!is_object($client)) {
                throw new InvalidArgumentException(sprintf('Expected object, "%s" given.', gettype($client)));
            }

            // Si ce n'est pas une instance de client XML-RPC
            if (!($client instanceof Client)) {
                throw new InvalidArgumentException(sprintf('Expected instance of class "%s", "%s" given.', Client::class, get_class($client)));
            }
        }

        $this->clients = $clients;

        return $this;
    }

    public function setCommonClient(Client $client): self
    {
        $this->clients[self::ENDPOINT_COMMON] = $client;

        return $this;
    }

    public function setObjectClient(Client $client): self
    {
        $this->clients[self::ENDPOINT_OBJECT] = $client;

        return $this;
    }

    public function getClients(): array
    {
        return $this->clients;
    }

    /**
     * @static
     */
    public static function getEndpoints(): array
    {
        return self::$endpoints;
    }

    /**
     * @static
     */
    public static function getDefaultMethods(): array
    {
        return self::$defaultMethods;
    }
}
