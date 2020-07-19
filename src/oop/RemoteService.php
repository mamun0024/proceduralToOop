<?php

namespace Oop;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class RemoteService
{
    protected $base_url;
    protected $request_endpoint;
    protected $request_data;
    protected $request_type;

    /**
     * Constructor
     */
    public function __construct()
    {
        // Some code.
    }

    /**
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->base_url;
    }

    /**
     * @param string $base_url
     */
    public function setBaseUrl($base_url): void
    {
        $this->base_url = $base_url;
    }

    /**
     * @return string
     */
    public function getRequestEndpoint()
    {
        return $this->request_endpoint;
    }

    /**
     * @param mixed $request_endpoint
     */
    public function setRequestEndpoint($request_endpoint): void
    {
        $this->request_endpoint = $request_endpoint;
    }

    /**
     * @return array
     */
    public function getRequestData()
    {
        return $this->request_data;
    }

    /**
     * @param array $request_data
     */
    public function setRequestData($request_data): void
    {
        $this->request_data = $request_data;
    }

    /**
     * @return string
     */
    public function getRequestType()
    {
        return $this->request_type;
    }

    /**
     * @param string $request_type
     */
    public function setRequestType($request_type): void
    {
        $this->request_type = $request_type;
    }

    /**
     * Http Request using Guzzle Http
     *
     * @return array $response
     * @throws GuzzleException
     */
    public function httpRequest()
    {
        $client = new Client([
            'base_uri' => $this->getBaseUrl(),
            'timeout' => 10.0,
            'verify' => false
        ]);

        if ($this->getRequestType() == "GET") {
            $request_body = [
                'query' => $this->getRequestData()
            ];
        } else {
            $request_body = [
                'form_params' => $this->getRequestData()
            ];
        }

        $response = $client->request(
            $this->getRequestType(),
            $this->getRequestEndpoint(),
            $request_body
        );

        return json_decode($response->getBody()->getContents(), true);
    }
}
