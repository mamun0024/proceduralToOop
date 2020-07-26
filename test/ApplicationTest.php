<?php

namespace Test;

use App\Application;
use App\Traits\HelperTrait;
use App\Traits\ResponseTrait;
use PHPUnit\Framework\TestCase;

class ApplicationTest extends TestCase
{
    use HelperTrait;
    use ResponseTrait;

    public function testInputValidation()
    {
        $app = new Application([
            'settings' => [
                'file_name'        => '',
                'file_path'        => '',
                'bin_check_url'    => '',
                'rate_url'         => '',
                'currency'         => '',
                'eu_commission'    => '',
                'ex_eu_commission' => '',
            ]
        ]);
        $response = $app->run();
        $this->assertStringContainsString('File name is required', $response);
        $this->assertStringContainsString('The Bin check url is required', $response);
        $this->assertStringContainsString('Rate url is required', $response);
        $this->assertStringContainsString('Currency is required', $response);
        $this->assertStringContainsString('The Eu commission is required', $response);
        $this->assertStringContainsString('The Ex eu commission is required', $response);

        $app2 = new Application([
            'settings' => [
                'file_name'        => 'input.txt',
                'file_path'        => 'files/',
                'bin_check_url'    => 'https://lookup.binlist.net',
                'rate_url'         => 'https://api.exchangeratesapi.io/latest',
                'currency'         => 'EUR',
                'eu_commission'    => 'eu_comm',
                'ex_eu_commission' => 'ex_eu_comm',
            ]
        ]);
        $response2 = $app2->run();
        $this->assertStringContainsString('The Eu commission must be numeric', $response2);
        $this->assertStringContainsString('The Ex eu commission must be numeric', $response2);
    }
}
