<?php

namespace Ang3\Component\Odoo;

use Ang3\Component\Odoo\Exception\RequestException;
use Ang3\Component\XmlRpc\Client as XmlRpcClient;
use Ang3\Component\XmlRpc\Exception\RemoteException as XmlRpcRemoteException;
use Psr\Log\LoggerInterface;

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
        $this->client = new XmlRpcClient($url);
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
            $result = $this->client->call($method, $args);
        } catch (\Throwable $e) {
            if ($e instanceof XmlRpcRemoteException && preg_match('#cannot marshal None unless allow_none is enabled#', $e->getMessage())) {
                $result = null;
            } else {
                throw new RequestException('XML-RPC request failed', 0, $e);
            }
        }

        if ($this->logger) {
            $this->logger->info('Request result: {result}', [
                'request_id' => $loggerContext['request_id'],
                is_scalar($result) ? $result : json_encode($result),
            ]);
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
