<?php

namespace App;

use App\Exceptions\RateUrlDataFormatException;
use App\Interfaces\RateFormatInterface;
use App\Traits\HelperTrait;

class RateFormat implements RateFormatInterface
{
    use HelperTrait;

    /**
     * @param $currency
     * @param $response
     * @return mixed
     * @throws RateUrlDataFormatException
     */
    public function fetchRate($currency, $response)
    {
        if (!$this->emptyCheck($response['rates'][$currency])) {
            throw new RateUrlDataFormatException();
        }
        return $response['rates'][$currency];
    }
}
