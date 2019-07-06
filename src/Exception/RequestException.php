<?php

namespace Ang3\Component\OdooApiClient\Exception;

use RuntimeException;
use Throwable;
use Ang3\Component\OdooApiClient\Client\ExternalApiClient;

/**
 * @author Joanis ROUANET
 */
class RequestException extends RuntimeException
{
    /**
     * @var ExternalApiClient
     */
    private $client;

    /**
     * @var string
     */
    private $method;

    /**
     * @var string
     */
    private $modelName;

    /**
     * @var array
     */
    private $parameters;

    /**
     * @var array
     */
    private $options;

    /**
     * Constructor of the exception.
     *
     * @param ExternalApiClient $client
     * @param string            $method
     * @param string            $modelName
     * @param array             $parameters
     * @param array             $options
     * @param array             $result
     */
    public function __construct(ExternalApiClient $client, $method, $modelName, array $parameters = [], array $options = [], array $result = [], Throwable $previous = null)
    {
        // Hydratation
        $this->client = $client;
        $this->method = $method;
        $this->modelName = $modelName;
        $this->parameters = $parameters;
        $this->options = $options;

        // Récupéraion du code et message d'erreur
        list($code, $message) = [
            !empty($result['faultCode']) ? $result['faultCode'] : 0,
            !empty($result['faultString']) ? $result['faultString'] : json_encode($result),
        ];

        // Construction de l'exception parent
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return ExternalApiClient
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return string
     */
    public function getModelName()
    {
        return $this->modelName;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }
}
