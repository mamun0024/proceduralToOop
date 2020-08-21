<?php

namespace App;

use App\Exceptions\RateUrlDataFormatException;
use App\Interfaces\RateFormatInterface;
use App\Traits\HelperTrait;
use GuzzleHttp\Exception\GuzzleException;

class RateFormat implements RateFormatInterface
{
    use HelperTrait;

    /**
     * @param string $url
     * @param string $currency
     * @return mixed
     * @throws RateUrlDataFormatException|GuzzleException
     */
    public function fetchRate($url, $currency)
    {
        $response = $this->callExternalUrl($url);

        if (!$this->emptyCheck($response['rates'][$currency])) {
            throw new RateUrlDataFormatException();
        }
        return $response['rates'][$currency];
    }
}
