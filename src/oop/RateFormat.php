<?php

namespace Oop;

use Oop\Exceptions\RateUrlDataFormatException;
use Oop\Interfaces\RateFormatInterface;
use Oop\Traits\HelperTrait;

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
