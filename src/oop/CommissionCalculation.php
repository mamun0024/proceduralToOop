<?php

namespace Oop;

use GuzzleHttp\Exception\GuzzleException;
use Oop\Interfaces\CountryCodeFormatInterface;
use Oop\Interfaces\RateFormatInterface;
use Oop\Traits\HelperTrait;
use Oop\Traits\ResponseTrait;
use Oop\Utils\EuCountryCodeList;

class CommissionCalculation
{
    use ResponseTrait;
    use HelperTrait;

    private $bin_url;
    private $rate_url;
    private $output_currency;

    private $eu_commission;
    private $except_eu_commission;

    private $country_code_interface;
    private $rate_interface;

    /**
     * @param $bin_url
     * @param $rate_url
     * @param $currency
     * @param $eu_comm
     * @param $ex_eu_comm
     * @param CountryCodeFormatInterface $country_code
     * @param RateFormatInterface $rate
     */
    public function __construct(
        $bin_url,
        $rate_url,
        $currency,
        $eu_comm,
        $ex_eu_comm,
        CountryCodeFormatInterface $country_code,
        RateFormatInterface $rate
    ) {
        $this->setBinUrl($bin_url);
        $this->setRateUrl($rate_url);
        $this->setOutputCurrency($currency);
        $this->setEuCommission($eu_comm);
        $this->setExceptEuCommission($ex_eu_comm);

        $this->country_code_interface = $country_code;
        $this->rate_interface = $rate;
    }

    /**
     * @return string
     */
    public function getBinUrl()
    {
        return $this->bin_url;
    }

    /**
     * @param string $bin_url
     */
    public function setBinUrl($bin_url): void
    {
        $this->bin_url = $bin_url;
    }

    /**
     * @return string
     */
    public function getRateUrl()
    {
        return $this->rate_url;
    }

    /**
     * @param string $rate_url
     */
    public function setRateUrl($rate_url): void
    {
        $this->rate_url = $rate_url;
    }

    /**
     * @return string
     */
    public function getOutputCurrency()
    {
        return $this->output_currency;
    }

    /**
     * @param string $output_currency
     */
    public function setOutputCurrency($output_currency): void
    {
        $this->output_currency = $output_currency;
    }

    /**
     * @return float
     */
    public function getEuCommission()
    {
        return $this->eu_commission;
    }

    /**
     * @param float $eu_commission
     */
    public function setEuCommission($eu_commission): void
    {
        $this->eu_commission = $eu_commission;
    }

    /**
     * @return float
     */
    public function getExceptEuCommission()
    {
        return $this->except_eu_commission;
    }

    /**
     * @param float $except_eu_commission
     */
    public function setExceptEuCommission($except_eu_commission): void
    {
        $this->except_eu_commission = $except_eu_commission;
    }

    /**
     * Checked the provided country
     * code included in Europe Union
     *
     * @param string $country_code Country code.
     * @return boolean
     */
    public function isEuropeUnion($country_code)
    {
        $list = new EuCountryCodeList();
        if (in_array($country_code, $list->list())) {
            $status = true;
        } else {
            $status = false;
        }
        return $status;
    }

    /**
     * Converting output currency
     *
     * @param float $value
     * @param string $from
     * @param string $to
     *
     * @return float
     * @throws GuzzleException
     */
    public function outputCurrency($value, $from, $to)
    {
        if ($from === $to) {
            $amount = $value;
        } else {
            $amount = $value * $this->getRate($to);
        }
        return $amount;
    }

    /**
     * dirty `ceil` type function with
     * precision capability.
     *
     * @param float $value
     * @param integer $precision
     *
     * @return float
     */
    public function ceiling($value, $precision = 0)
    {
        return ceil($value * pow(10, $precision)) / pow(10, $precision);
    }

    /**
     * Call Bin Check URL.
     *
     * @param string $base_url
     * @param string $endpoint
     * @param array $data
     * @param string $type
     *
     * @return array
     * @throws GuzzleException
     */
    public function callExternalUrl($base_url, $endpoint, $data = [], $type = "GET")
    {
        $remote_service = new RemoteService();
        $remote_service->setBaseUrl($base_url);
        $remote_service->setRequestEndpoint($endpoint);
        $remote_service->setRequestData($data);
        $remote_service->setRequestType($type);

        return $remote_service->httpRequest();
    }

    /**
     * Country Code data format.
     *
     * @param $response_data
     *
     * @return float|integer
     */
    public function fetchCountryCode($response_data)
    {
        return $this->country_code_interface->fetchCountryCode($response_data);
    }

    /**
     * Call Bin Check URL.
     *
     * @param integer $bin
     *
     * @return string
     * @throws GuzzleException
     */
    public function getCountryCode($bin)
    {
        $response_data = $this->callExternalUrl($this->getBinUrl(), $bin);
        return $this->fetchCountryCode($response_data);
    }

    /**
     * Rate data format.
     *
     * @param string $row_currency
     * @param $response_data
     *
     * @return float|integer
     */
    public function fetchRate($row_currency, $response_data)
    {
        return $this->rate_interface->fetchRate($row_currency, $response_data);
    }

    /**
     * Call Rate URL.
     *
     * @param string $row_currency
     *
     * @return float|integer
     * @throws GuzzleException
     */
    public function getRate($row_currency)
    {
        $response_data = $this->callExternalUrl($this->getRateUrl(), $this->getRateUrl());
        if ($row_currency != "EUR") {
            $rate = $this->fetchRate($row_currency, $response_data);
        } else {
            $rate = 0;
        }
        return $rate;
    }

    /**
     * Read file & Calculate
     * the the data.
     *
     * @param array $rowData
     *
     * @return array
     * @throws GuzzleException
     */
    public function calculateData($rowData)
    {
        $data = [];
        foreach ($rowData as $value) {
            $country_code = $this->getCountryCode($value['bin']);
            $rate         = $this->getRate($value['currency']);

            if ($rate == 0) {
                $amount = $value['amount'];
            } else {
                $amount = $value['amount'] / $rate;
            }

            $commission = $this->outputCurrency(
                $amount * ($this->isEuropeUnion($country_code) ?
                    $this->getEuCommission() : $this->getExceptEuCommission()),
                "EUR",
                $this->getOutputCurrency()
            );

            $ceil_with_precision = $this->ceiling($commission, 2);
            $data[] = number_format($ceil_with_precision, 2, '.', '');
        }
        return $data;
    }
}
