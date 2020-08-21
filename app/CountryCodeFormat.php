<?php

namespace App;

use App\Exceptions\BinCheckUrlDataFormatException;
use App\Exceptions\RateUrlDataFormatException;
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
        $response = $this->callExternalUrl($url, $bin);

        if (!$this->emptyCheck($response['country']['alpha2'])) {
            throw new BinCheckUrlDataFormatException();
        } else {
            return $response['country']['alpha2'];
        }
    }
}
