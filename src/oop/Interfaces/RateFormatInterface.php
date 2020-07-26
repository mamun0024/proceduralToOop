<?php

namespace Oop\Interfaces;

interface RateFormatInterface
{
    public function fetchRate($currency, $response);
}
