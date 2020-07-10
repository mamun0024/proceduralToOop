<?php

namespace Oop;

class CalculateCommission
{
    private $settings;

    public function __construct($settings = [])
    {
        $this->settings = $settings['settings'];
    }
}