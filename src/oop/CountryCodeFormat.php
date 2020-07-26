<?php

namespace Oop;

use Oop\Exceptions\BinCheckUrlDataFormatException;
use Oop\Interfaces\CountryCodeFormatInterface;
use Oop\Traits\HelperTrait;

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
