<?php

namespace Ang3\Component\Odoo;

use Ang3\Component\Odoo\Exception\RemoteException;
use Ang3\Component\Odoo\Exception\RequestException;
use Ang3\Component\XmlRpc\Client as XmlRpcClient;
use Ang3\Component\XmlRpc\Exception\RemoteException as XmlRpcRemoteException;
use Throwable;

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
        $this->client = new JsonRpcClient($url);
    }

    /**
     * @return mixed
     * @throws RequestException when request failed
     *
     */
    public function call(string $service, $method, array $args = [])
    {
        try {
            return $this->client->call("call", ["service" => $service, "method" => $method, "args" => $args]);
        } catch (XmlRpcRemoteException $exception) {
            if (preg_match('#cannot marshal None unless allow_none is enabled#', $exception->getMessage())) {
                return null;
            }

            throw RemoteException::create($exception);
        } catch (Throwable $exception) {
            throw new RequestException($exception->getMessage(), $exception->getCode(), $exception);
        }
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
