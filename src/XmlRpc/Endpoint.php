<?php

namespace Ang3\Component\Odoo\XmlRpc;

use Ang3\Component\Odoo\Exception\RemoteException;
use Ang3\Component\Odoo\Exception\RequestException;
use PhpXmlRpc\Client;
use PhpXmlRpc\Request;
use Psr\Log\LoggerInterface;

class Endpoint
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var EncoderInterface
     */
    protected $encoder;

    /**
     * @var LoggerInterface|null
     */
    private $logger;

    public function __construct(string $path, LoggerInterface $logger = null, EncoderInterface $encoder = null)
    {
        $this->client = new Client($path);
        $this->client->return_type = 'xml';
        $this->encoder = $encoder ?: new Encoder();
        $this->logger = $logger;
    }

    public function __toString(): string
    {
        return $this->client->path;
    }

    /**
     * @throws RemoteException  when a fault code as been returned
     * @throws RequestException on endpoint request error
     *
     * @return mixed
     */
    public function call(string $method, array $args = [], array $loggerContext = [])
    {
        $loggerContext['endpoint'] = $this->client->path;

        foreach ($args as $key => $arg) {
            $args[$key] = $this->encoder->encode($arg);
        }

        $context['encoded_data'] = $args;

        if ($this->logger) {
            $this->logger->debug('XML-RPC request with method "{method}"', $loggerContext);
        }

        try {
            $response = $this->client->send(new Request($method, $args));
        } catch (\Throwable $e) {
            throw new RequestException('Request failed', 0, $e);
        }

        $response = is_array($response) ? array_pop($response) : $response;

        if (null === $response) {
            return null;
        }

        if (0 !== ($faultCode = $response->faultCode())) {
            throw RemoteException::createFromXmlResult($faultCode, $response->faultString() ?: 'Unknown error');
        }

        if ($this->logger) {
            $this->logger->debug('XML-RPC response received', $loggerContext);
        }

        $data = $this->encoder->decode($response->val);

        if ($this->logger) {
            $this->logger->debug('XML-RPC response decoded', array_merge($loggerContext, [
                'data_type' => gettype($data),
            ]));
        }

        if (is_array($data) && !empty($data['fault'])) {
            $error = [
                'code' => (int) ($data['fault']['faultCode'] ?? 0),
                'message' => (string) ($data['fault']['faultString'] ?? 'Unknown error'),
            ];

            if ($this->logger) {
                $this->logger->debug('XML-RPC response error: {message} - Code {code}', array_merge($loggerContext, $error));
            }

            throw RemoteException::createFromXmlResult($error['code'], $error['message']);
        }

        return $data['params']['param'] ?? $data;
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function getEncoder(): EncoderInterface
    {
        return $this->encoder;
    }

    public function setEncoder(EncoderInterface $encoder): self
    {
        $this->encoder = $encoder;

        return $this;
    }

    public function getLogger(): ?LoggerInterface
    {
        return $this->logger;
    }

    public function setLogger(?LoggerInterface $logger): self
    {
        $this->logger = $logger;

        return $this;
    }
}
