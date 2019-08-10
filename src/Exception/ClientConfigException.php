<?php

namespace Ang3\Component\OdooApiClient\Exception;

use Throwable;

/**
 * @author Joanis ROUANET
 */
class ClientConfigException extends OdooException
{
    /**
     * @var array
     */
    private $config;

    /**
     * Constructor of the exception.
     *
     * @param string         $message
     * @param array          $config
     * @param Throwable|null $previous
     */
    public function __construct(string $message, array $config = [], Throwable $previous = null)
    {
        // Hydratation
        $this->config = $config;

        // Construction de l'exception parent
        parent::__construct($message, 0, $previous);
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }
}
