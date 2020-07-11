<?php

namespace Oop\Traits;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

trait HelperTrait
{
    /**
     * Given value emptiness check.
     *
     * @param string $value
     * @return bool
     */
    public function emptyCheck($value)
    {
        if (isset($value) && ($value != null) && ($value != '')) {
            $status = true;
        } else {
            $status = false;
        }
        return $status;
    }

    /**
     * Http Request using Guzzle Http
     *
     * @param string $base_url
     * @param array $request_data
     * @param string $request_endpoint
     * @param string $request_type
     *
     * @return mixed $response
     * @throws GuzzleException
     */
    public function httpRequest($base_url, $request_endpoint, $request_data, $request_type = "GET")
    {
        $client = new Client([
            'base_uri' => $base_url,
            'timeout' => 10.0,
            'verify' => false
        ]);

        if ($request_type == "GET") {
            $request_body = [
                'query' => $request_data
            ];
        } else {
            $request_body = [
                'form_params' => $request_data,
            ];
        }

        return $client->request($request_type, $request_endpoint, $request_body);
    }
}