<?php

namespace App\Interfaces;

interface RateFormatInterface
{
    public function fetchRate($currency, $response);
}
