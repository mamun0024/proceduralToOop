<?php

namespace App;

use App\Exceptions\BinCheckUrlDataFormatException;
use App\Interfaces\CountryCodeFormatInterface;
use App\Traits\HelperTrait;

class CountryCodeFormat implements CountryCodeFormatInterface
{
    use HelperTrait;

    /**
     * @param $response
     * @return mixed
     * @throws BinCheckUrlDataFormatException
     */
    public function fetchCountryCode($response)
    {
        if (!$this->emptyCheck($response['country']['alpha2'])) {
            throw new BinCheckUrlDataFormatException();
        } else {
            return $response['country']['alpha2'];
        }
    }
}
