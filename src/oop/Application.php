<?php

namespace Oop;

class Application
{
    private $settings;

    public function __construct($settings = [])
    {
        $this->settings = $settings['settings'];
    }

    public function run()
    {
        $calculate_commission = new CalculateCommission();
        $calculate_commission->calculate([
            'file_name' => $this->settings['file_name'],
            'bin_url'   => $this->settings['bin_check_url'],
            'rate_url'  => $this->settings['rate_url'],
            'currency'  => $this->settings['currency']
        ]);
    }
}
