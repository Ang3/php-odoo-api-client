<?php

namespace Ang3\Component\Odoo;

use Ang3\Component\Odoo\Exception\RequestException;
use Psr\Log\LoggerInterface;
use Ripcord\Client\Client as XmlRpcClient;
use Ripcord\Exceptions\RemoteException as XmlRpcRemoteException;
use Ripcord\Ripcord;

class Endpoint
{
    /**
     * @var string
     */
    private $url;

    /**
     * @var XmlRpcClient
     */
    private $client;

    /**
     * @var LoggerInterface|null
     */
    private $logger;

    public function __construct(string $url, LoggerInterface $logger = null)
    {
        $this->url = $url;
        $this->client = Ripcord::client($url);
        $this->client->_throwExceptions = true;
        $this->logger = $logger;
    }

    /**
     * @throws RequestException when request failed
     *
     * @return mixed
     */
    public function call(string $method, array $args = [])
    {
        $loggerContext = [
            'request_id' => uniqid('xmlrpc-request-', true),
            'url' => $this->url,
            'method' => $method,
            'arguments' => $args,
        ];

        if ($this->logger) {
            $this->logger->info('Calling method {url}::{method}', $loggerContext);
        }

        try {
            $result = $this->client->__call($method, $args);
        } catch (\Throwable $e) {
            if ($e instanceof XmlRpcRemoteException) {
                if (preg_match('#cannot marshal None unless allow_none is enabled#', $e->getMessage())) {
                    $result = null;
                } else {
                    throw new RequestException($e->getMessage(), $e->getCode());
                }
            } else {
                throw new RequestException('An error occurred during the request', 0, $e);
            }
        }

        if ($this->logger) {
            $loggerContext['result'] = is_scalar($result) ? $result : json_encode($result);
            $this->logger->info('Request result: {result}', $loggerContext);
        }

        return $result;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getClient(): XmlRpcClient
    {
        return $this->client;
    }

    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }
}
