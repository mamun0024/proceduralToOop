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

use GuzzleHttp\Exception\GuzzleException;
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

    public function testCalculateDataFunction()
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

    public function testGetRateFunction()
    {
        $com_cal = $this->getMockBuilder(CommissionCalculate::class)
            ->setMethods(array('callExternalUrl'))
            ->getMock();

        $com_cal->expects($this->any())
            ->method('callExternalUrl')
            ->will($this->returnValue(['rates' => ['USD' => 1.1276]]));

        $this->assertEquals(1.1276, $com_cal->getRate('USD'));
        $this->assertEquals(0, $com_cal->getRate('EUR'));

        try {
            $com_cal->expects($this->any())
                ->method('callExternalUrl')
                ->willThrowException(new RateUrlDataFormatException());
            $com_cal->getRate('USD');
        } catch (RateUrlDataFormatException $e) {
            $this->assertStringContainsString('Rate url response data format is not as expected.', $e->errorMessage());
        }
    }

    public function testGetCountryCodeFunction()
    {
        $com_cal = $this->getMockBuilder(CommissionCalculate::class)
            ->setMethods(array('callExternalUrl'))
            ->getMock();

        $com_cal->expects($this->any())
            ->method('callExternalUrl')
            ->will($this->returnValue(['country' => ['alpha2' => 'LT']]));

        $this->assertEquals('LT', $com_cal->getCountryCode('516793'));

        try {
            $com_cal->expects($this->any())
                ->method('callExternalUrl')
                ->willThrowException(new BinCheckUrlDataFormatException());
            $com_cal->getCountryCode('516793');
        } catch (BinCheckUrlDataFormatException $e) {
            $this->assertStringContainsString('Bin url response data format is not as expected.', $e->errorMessage());
        }
    }

    public function testCeilingFunction()
    {
        $this->assertEquals(0.47, $this->comm_cal->ceiling(0.46180844185832, 2));
        $this->assertEquals(1.66, $this->comm_cal->ceiling(1.6574127786525, 2));
        $this->assertEquals(2.41, $this->comm_cal->ceiling(2.4014038976632, 2));
        $this->assertEquals(43.72, $this->comm_cal->ceiling(43.714413735069, 2));
    }

    public function testOutputCurrencyFunction()
    {
        $com_cal = $this->getMockBuilder(CommissionCalculate::class)
            ->setMethods(array('getRate'))
            ->getMock();

        $com_cal->expects($this->any())
            ->method('getRate')
            ->will($this->returnValue(1.1276));

        $this->assertEquals(0.46180844185832, $com_cal->outputCurrency(0.46180844185832, "EUR", "EUR"));
        $this->assertEquals(0.52073519903944, $com_cal->outputCurrency(0.46180844185832, "EUR", "USD"));
    }

    public function testIsEuropeUnionFunction()
    {
        $this->assertFalse($this->comm_cal->isEuropeUnion("BD"));
        $this->assertTrue($this->comm_cal->isEuropeUnion("DK"));
    }
}
