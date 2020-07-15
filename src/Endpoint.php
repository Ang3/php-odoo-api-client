<?php

namespace Ang3\Component\Odoo;

use Ang3\Component\Odoo\Exception\RequestException;
use Ang3\Component\XmlRpc\Client as XmlRpcClient;
use Ang3\Component\XmlRpc\Exception\RemoteException as XmlRpcRemoteException;

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

    public function __construct(string $url)
    {
        $this->url = $url;
        $this->client = new XmlRpcClient($url);
    }

    /**
     * @throws RequestException when request failed
     *
     * @return mixed
     */
    public function call(string $method, array $args = [])
    {
        try {
            $result = $this->client->call($method, $args);
        } catch (\Throwable $e) {
            if ($e instanceof XmlRpcRemoteException && preg_match('#cannot marshal None unless allow_none is enabled#', $e->getMessage())) {
                $result = null;
            } else {
                throw new RequestException('XML-RPC request failed', 0, $e);
            }
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
}
