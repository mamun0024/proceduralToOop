<?php

namespace Test;

require_once __DIR__ . '/../src/oop/Exceptions/BaseException.php';
require_once __DIR__ . '/../src/oop/Exceptions/BinCheckUrlDataFormatException.php';
require_once __DIR__ . '/../src/oop/Exceptions/FileDataFormatException.php';
require_once __DIR__ . '/../src/oop/Exceptions/FileNotExistsException.php';
require_once __DIR__ . '/../src/oop/Exceptions/RateUrlDataFormatException.php';
require_once __DIR__ . '/../src/oop/Requests/Request.php';
require_once __DIR__ . '/../src/oop/Requests/CalculateCommissionRequest.php';
require_once __DIR__ . '/../src/oop/Traits/HelperTrait.php';
require_once __DIR__ . '/../src/oop/Traits/ResponseTrait.php';
require_once __DIR__ . '/../src/oop/Utils/EuCountryCodeList.php';
require_once __DIR__ . '/../src/oop/CommissionFile.php';
require_once __DIR__ . '/../src/oop/CommissionCalculate.php';

use Oop\CommissionCalculate;
use Oop\CommissionFile;
use Oop\Exceptions\BinCheckUrlDataFormatException;
use Oop\Exceptions\RateUrlDataFormatException;
use Oop\Traits\HelperTrait;
use Oop\Traits\ResponseTrait;
use PHPUnit\Framework\TestCase;

class CommissionCalculateTest extends TestCase
{
    use HelperTrait;
    use ResponseTrait;

    private $comm_cal;

    protected function setUp(): void
    {
        $this->comm_cal = new CommissionCalculate();
    }

    public function testInputValidation()
    {
        $response = $this->comm_cal->calculate([
            'file_name' => '',
            'file_path' => '',
            'bin_url'   => '',
            'rate_url'  => '',
            'currency'  => '',
            'eu_comm'   => '',
            'ex_eu_comm' => '',
        ]);
        $this->assertStringContainsString('File name is required', $response);
        $this->assertStringContainsString('Bin url is required', $response);
        $this->assertStringContainsString('Rate url is required', $response);
        $this->assertStringContainsString('Currency is required', $response);
        $this->assertStringContainsString('Eu comm is required', $response);
        $this->assertStringContainsString('Ex eu comm is required', $response);

        $response2 = $this->comm_cal->calculate([
            'file_name' => 'input.txt',
            'file_path' => 'files/',
            'bin_url'   => 'https://lookup.binlist.net',
            'rate_url'  => 'https://api.exchangeratesapi.io/latest',
            'currency'  => 'EUR',
            'eu_comm'   => 'eu_comm',
            'ex_eu_comm' => 'ex_eu_comm',
        ]);
        $this->assertStringContainsString('Eu comm must be numeric', $response2);
        $this->assertStringContainsString('Ex eu comm must be numeric', $response2);
    }

    public function testCalculateDataReturnData()
    {
        $com_cal = $this->getMockBuilder(CommissionCalculate::class)
            ->setMethods(array('getCountryCode', 'getRate', 'outputCurrency'))
            ->getMock();

        $com_cal->expects($this->any())
            ->method('getCountryCode')
            ->will($this->returnValue('LT'));

        $com_cal->expects($this->any())
            ->method('getRate')
            ->will($this->returnValue(1.1276));

        $com_cal->expects($this->any())
            ->method('outputCurrency')
            ->will($this->returnValue(0.46180844185832));

        $this->assertEquals(0.47, $com_cal->calculateData([
            [
                "bin" => 516793,
                "amount" => 50.00,
                "currency" => "USD"
            ]
        ])[0]);
    }
}
