<?php

namespace App\Traits;

use App\RemoteService;
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
     * Generate http request.
     *
     * @param string $base_url
     * @param string $endpoint
     * @param array $data
     * @param string $type
     *
     * @return array
     * @throws GuzzleException
     */
    public function callExternalUrl($base_url, $endpoint = null, $data = [], $type = "GET")
    {
        $endpoint = ($this->emptyCheck($endpoint)) ? $endpoint : $base_url;

        $remote_service = new RemoteService();
        $remote_service->setBaseUrl($base_url);
        $remote_service->setRequestEndpoint($endpoint);
        $remote_service->setRequestData($data);
        $remote_service->setRequestType($type);

        return $remote_service->httpRequest();
    }
}
