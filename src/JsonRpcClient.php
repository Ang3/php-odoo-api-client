<?php

namespace Ang3\Component\Odoo;

class JsonRpcClient {

    private $url;

    public function __construct($url) {
        $this->url = $url;
    }

    /**
     * @throws \Exception
     */
    public function call($method, $params = []) {
        $content = json_encode([
            'jsonrpc' => '2.0',
            'method' => $method,
            'id' => uniqid('rpc', true),
            'params' => $params
        ]);

        $context = stream_context_create(['http' => ['method' => 'POST', 'header' => 'Content-Type: application/json', 'content' => $content]]);

        $request = file_get_contents($this->getUrl(), false, $context);

        if ($request === false) {
            throw new \Exception('Unable to connect to Odoo');
        }
        $data = json_decode($request, true);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \Exception("Failed to parse json: " . json_last_error_msg());
        }
        if(isset($data['error'])) {
            throw new \Exception($data['error']['message']);
        }
        return $data['result'];
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }
}