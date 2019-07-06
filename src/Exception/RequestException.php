<?php

namespace Ang3\Component\OdooApiClient\Exception;

use RuntimeException;
use Throwable;

/**
 * @author Joanis ROUANET
 */
class RequestException extends RuntimeException
{
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
     * @param string $method
     * @param string $modelName
     * @param array  $parameters
     * @param array  $options
     * @param array  $result
     */
    public function __construct($method, $modelName, array $parameters = [], array $options = [], array $result = [], Throwable $previous = null)
    {
        // Hydratation
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
        parent::__construct($code, $message, $previous);
    }
}
