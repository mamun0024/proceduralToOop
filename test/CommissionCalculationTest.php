<?php

namespace Test;

use App\CommissionCalculation;
use App\CountryCodeFormat;
use App\Exceptions\BinCheckUrlDataFormatException;
use App\Exceptions\RateUrlDataFormatException;
use App\RateFormat;
use App\Traits\HelperTrait;
use App\Traits\ResponseTrait;
use PHPUnit\Framework\TestCase;

class CommissionCalculationTest extends TestCase
{
    use HelperTrait;
    use ResponseTrait;

    private $comm_cal;

    protected function setUp(): void
    {
        $this->comm_cal = new CommissionCalculation(
            'https://test_bin',
            'https://test_rate',
            'EUR',
            '0.01',
            '0.02',
            new CountryCodeFormat(),
            new RateFormat()
        );
    }

    public function testCalculateDataFunction()
    {
        $com_cal = $this->getMockBuilder(CommissionCalculation::class)
            ->disableOriginalConstructor()
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
        $com_cal = $this->getMockBuilder(CommissionCalculation::class)
            ->disableOriginalConstructor()
            ->setMethods(array('fetchRate'))
            ->getMock();

        $com_cal->expects($this->any())
            ->method('fetchRate')
            ->will($this->returnValue(1.1276));

        $this->assertEquals(1.1276, $com_cal->getRate('USD'));
        $this->assertEquals(0, $com_cal->getRate('EUR'));

        try {
            $com_cal->expects($this->any())
                ->method('fetchRate')
                ->willThrowException(new RateUrlDataFormatException());
            $com_cal->getRate('USD');
        } catch (RateUrlDataFormatException $e) {
            $this->assertStringContainsString('Rate url response data format is not as expected.', $e->errorMessage());
        }
    }

    public function testGetCountryCodeFunction()
    {
        $com_cal = $this->getMockBuilder(CommissionCalculation::class)
            ->disableOriginalConstructor()
            ->setMethods(array('fetchCountryCode'))
            ->getMock();

        $com_cal->expects($this->any())
            ->method('fetchCountryCode')
            ->will($this->returnValue('LT'));

        $this->assertEquals('LT', $com_cal->getCountryCode('516793'));

        try {
            $com_cal->expects($this->any())
                ->method('fetchCountryCode')
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
        $com_cal = $this->getMockBuilder(CommissionCalculation::class)
            ->disableOriginalConstructor()
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
