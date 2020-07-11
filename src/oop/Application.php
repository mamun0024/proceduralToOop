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
        $calculate_commission = new CommissionCalculate();
        $calculate_commission->calculate([
            'file_name' => $this->settings['file_name'],
            'file_path' => $this->settings['file_path'],
            'bin_url'   => $this->settings['bin_check_url'],
            'rate_url'  => $this->settings['rate_url'],
            'currency'  => $this->settings['currency'],
            'eu_comm'   => $this->settings['eu_commission'],
            'ex_eu_comm' => $this->settings['ex_eu_commission']
        ]);
    }
}
