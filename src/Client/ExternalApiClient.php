<?php

namespace Ang3\Component\OdooApiClient\Client;

use InvalidArgumentException;
use Ang3\Component\OdooApiClient\Exception\RequestException;
use Ripcord\Ripcord;
use Ripcord\Client\Client;

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
     * Object methods.
     */
    const METHOD_CREATE = 'create';
    const METHOD_READ = 'read';
    const METHOD_WRITE = 'write';
    const METHOD_DELETE = 'delete';
    const METHOD_SEARCH = 'search';
    const METHOD_SEARCH_COUNT = 'search_count';
    const METHOD_SEARCH_READ = 'search_read';
    const METHOD_LIST_FIELDS = 'fields_get';

    /**
     * API endpoints list.
     *
     * @static
     *
     * @var array
     */
    private static $endpoints = array(
        self::ENDPOINT_COMMON,
        self::ENDPOINT_OBJECT,
    );

    /**
     * API object methods list.
     *
     * @static
     *
     * @var array
     */
    private static $objectMethods = array(
        self::METHOD_CREATE,
        self::METHOD_READ,
        self::METHOD_WRITE,
        self::METHOD_DELETE,
        self::METHOD_SEARCH,
        self::METHOD_SEARCH_COUNT,
        self::METHOD_SEARCH_READ,
        self::METHOD_LIST_FIELDS,
    );

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

    /**
     * Constructor of the client.
     *
     * @param string $host
     * @param string $database
     * @param string $user
     * @param string $password
     * @param array  $options
     */
    public function __construct($host, $database, $user, $password, array $options = [])
    {
        $this->host = $host;
        $this->database = $database;
        $this->user = $user;
        $this->password = $password;
        $this->options = $options;
    }

    //
    // ---------------------------------------------------------------------------------------
    // --- Setters & Getters
    // ---------------------------------------------------------------------------------------
    //

    /**
     * Get endpoints.
     *
     * @static
     *
     * @return array
     */
    public static function getEndpoints()
    {
        return self::$endpoints;
    }

    /**
     * Get object methods.
     *
     * @static
     *
     * @return array
     */
    public static function getObjectMethods()
    {
        return self::$objectMethods;
    }

    /**
     * Set host.
     *
     * @param string $host
     *
     * @return self
     */
    public function setHost($host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * Get host.
     *
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Set database.
     *
     * @param string $database
     *
     * @return self
     */
    public function setDatabase($database)
    {
        $this->database = $database;

        return $this;
    }

    /**
     * Get database.
     *
     * @return string
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * Set user.
     *
     * @param string $user
     *
     * @return self
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user.
     *
     * @return string
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set password.
     *
     * @param string $password
     *
     * @return self
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password.
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set options.
     *
     * @param array $options
     *
     * @return self
     */
    public function setOptions(array $options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Get options.
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Set clients.
     *
     * @param array $clients
     *
     * @throws InvalidArgumentException when at least one client is not valid
     *
     * @return self
     */
    public function setClients(array $clients)
    {
        // Pour chaque client
        foreach ($clients as $client) {
            // Si ce n'est pas un objet
            if (!is_object($client)) {
                throw new InvalidArgumentException('Expected object, "%s" given.', gettype($client));
            }

            // Si ce n'est pas une instance de client XML-RPC
            if (!($client instanceof Client)) {
                throw new InvalidArgumentException('Expected instance of class "%s", "%s" given.', Client::class, get_class($client));
            }
        }

        $this->clients = $clients;

        return $this;
    }

    /**
     * Set the common client.
     *
     * @param Client $client
     *
     * @return self
     */
    public function setCommonClient(Client $client)
    {
        $this->clients[self::ENDPOINT_COMMON] = $client;

        return $this;
    }

    /**
     * Set the object client.
     *
     * @param Client $client
     *
     * @return self
     */
    public function setObjectClient(Client $client)
    {
        $this->clients[self::ENDPOINT_OBJECT] = $client;

        return $this;
    }

    /**
     * Get clients.
     *
     * @return array
     */
    public function getClients()
    {
        return $this->clients;
    }

    //
    // ---------------------------------------------------------------------------------------
    // --- Client calls
    // ---------------------------------------------------------------------------------------
    //

    /**
     * Get used API version.
     *
     * @return string
     */
    public function version()
    {
        return $this
            ->getXmlRpcClient(self::ENDPOINT_COMMON)
            ->version()
        ;
    }

    /**
     * Create a record.
     *
     * @param string $modelName
     * @param array  $parameters
     * @param array  $options
     *
     * @return array
     */
    public function create($modelName, array $parameters = [], array $options = [])
    {
        return $this->call($modelName, self::METHOD_CREATE, $parameters, $options);
    }

    /**
     * Read models.
     *
     * @param string $modelName
     * @param array  $parameters
     * @param array  $options
     *
     * @return array
     */
    public function read($modelName, array $parameters, array $options = [])
    {
        return $this->call($modelName, self::METHOD_READ, $parameters, $options);
    }

    /**
     * Update a record.
     *
     * @param string $modelName
     * @param array  $parameters
     * @param array  $options
     *
     * @return array
     */
    public function update($modelName, array $parameters = [], array $options = [])
    {
        return $this->call($modelName, self::METHOD_WRITE, $parameters, $options);
    }

    /**
     * Delete models.
     *
     * @param string $modelName
     * @param array  $parameters
     * @param array  $options
     *
     * @return array
     */
    public function delete($modelName, array $parameters, array $options = [])
    {
        return $this->call($modelName, self::METHOD_DELETE, $parameters, $options);
    }

    /**
     * List model fields.
     *
     * @param string $modelName
     * @param array  $parameters
     * @param array  $options
     *
     * @return array
     */
    public function listFields($modelName, array $parameters = [], array $options = [])
    {
        return $this->call($modelName, self::METHOD_LIST_FIELDS, $parameters, $options);
    }

    /**
     * Search and read models.
     *
     * @param string $modelName
     * @param array  $parameters
     * @param array  $options
     *
     * @return array
     */
    public function searchAndRead($modelName, array $parameters = [], array $options = [])
    {
        return $this->call($modelName, self::METHOD_SEARCH_READ, $parameters, $options);
    }

    /**
     * Search models.
     *
     * @param string $modelName
     * @param array  $parameters
     * @param array  $options
     *
     * @return array
     */
    public function search($modelName, array $parameters = [], array $options = [])
    {
        return $this->call($modelName, self::METHOD_SEARCH, $parameters, $options);
    }

    /**
     * Count models.
     *
     * @param string $modelName
     * @param array  $parameters
     * @param array  $options
     *
     * @return array
     */
    public function count($modelName, array $parameters = [], array $options = [])
    {
        return $this->call($modelName, self::METHOD_SEARCH_COUNT, $parameters, $options);
    }

    /**
     * Call method of Odoo model.
     *
     * @param string $name       The model name
     * @param string $method     The method name
     * @param array  $parameters An array/list of parameters passed by position
     * @param array  $options    A mapping/dict of parameters to pass by keyword (optional)
     *
     * @throws InvalidArgumentException when the API object method is unknown
     *
     * @return mixed
     */
    public function call($name, $method, array $parameters = [], array $options = [])
    {
        if (!$this->checkObjectMethod($method)) {
            throw new InvalidArgumentException(sprintf('Unknown API object method "%s".', $method));
        }

        // Lancement de la requête et récupération des données
        $data = $this
            ->getXmlRpcClient(self::ENDPOINT_OBJECT)
            ->execute_kw($this->database, $this->getUid(), $this->password, $name, $method, [$parameters], $options)
        ;

        // Si les données représente une erreur
        if (is_array($data) && !empty($data['faultCode'])) {
            throw new RequestException($name, $method, [$parameters], $options, $data);
        }

        // Retour des données
        return $data;
    }

    /**
     * Get the uid of logged client.
     *
     * @param bool $forceAuthentication Force authentication
     *
     * @return int
     */
    public function getUid($forceAuthentication = false)
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
     *
     * @return Client
     */
    public function getXmlRpcClient($endpoint = self::ENDPOINT_COMMON, $refresh = false)
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
     *
     * @param string $endpoint
     *
     * @return bool
     */
    public function checkEndpoint($endpoint)
    {
        return in_array($endpoint, self::$endpoints);
    }

    /**
     * Check if the given object method is valid.
     *
     * @param string $method
     *
     * @return bool
     */
    public function checkObjectMethod($method)
    {
        return in_array($method, self::$objectMethods);
    }
}
