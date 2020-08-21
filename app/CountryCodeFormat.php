<?php

namespace App;

use App\Exceptions\BinCheckUrlDataFormatException;
use App\Interfaces\CountryCodeFormatInterface;
use App\Traits\HelperTrait;
use GuzzleHttp\Exception\GuzzleException;

class CountryCodeFormat implements CountryCodeFormatInterface
{
    use HelperTrait;

    /**
     * @param string $url
     * @param string $bin
     * @return mixed
     * @throws BinCheckUrlDataFormatException|GuzzleException
     */
    public function fetchCountryCode($url, $bin)
    {
        $remote_service = new RemoteService($url, $bin);
        $response = $remote_service->httpRequest();

        if (!$this->emptyCheck($response['country']['alpha2'])) {
            throw new BinCheckUrlDataFormatException();
        } else {
            return $response['country']['alpha2'];
        }
    }
}
