<?php

namespace App\Interfaces;

interface CountryCodeFormatInterface
{
    public function fetchCountryCode($url, $bin);
}
