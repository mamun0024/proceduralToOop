<?php

namespace Oop;

use GuzzleHttp\Exception\GuzzleException;
use Oop\Exceptions\FileDataFormatException;
use Oop\Exceptions\FileNotExistsException;
use Oop\Exceptions\BinCheckUrlDataFormatException;
use Oop\Exceptions\RateUrlDataFormatException;
use Oop\Traits\HelperTrait;
use Oop\Traits\ResponseTrait;
use Oop\Requests\CalculateCommissionRequest;
use Oop\Utils\EuCountryCodeList;

class CommissionCalculate extends CommissionFile
{
    use ResponseTrait;
    use HelperTrait;

    private $bin_url;
    private $rate_url;
    private $output_currency;

    private $eu_commission;
    private $except_eu_commission;

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
     * @param array $inputs
     */
    public function setAllData($inputs)
    {
        $this->setFileName($inputs['file_name']);
        $this->setBinUrl($inputs['bin_url']);
        $this->setRateUrl($inputs['rate_url']);
        $this->setOutputCurrency($inputs['currency']);
        $this->setEuCommission($inputs['eu_comm']);
        $this->setExceptEuCommission($inputs['ex_eu_comm']);
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
     * @throws RateUrlDataFormatException
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
        $http_base_url = $base_url;
        $http_endpoint = $endpoint;
        $http_data     = $data;
        $http_type     = $type;

        // Call api.
        $http_response  = $this->httpRequest(
            $http_base_url,
            $http_endpoint,
            $http_data,
            $http_type
        );
        return json_decode($http_response->getBody()->getContents(), true);
    }

    /**
     * Call Bin Check URL.
     *
     * @param integer $bin
     *
     * @return string
     * @throws GuzzleException
     * @throws BinCheckUrlDataFormatException
     */
    public function getCountryCode($bin)
    {
        $response_data = $this->callExternalUrl($this->getBinUrl(), $bin);
        if (!$this->emptyCheck($response_data['country']['alpha2'])) {
            throw new BinCheckUrlDataFormatException();
        } else {
            return $response_data['country']['alpha2'];
        }
    }

    /**
     * Call Rate URL.
     *
     * @param string $row_currency
     *
     * @return float|integer
     * @throws GuzzleException
     * @throws RateUrlDataFormatException
     */
    public function getRate($row_currency)
    {
        $response_data = $this->callExternalUrl($this->getRateUrl(), $this->getRateUrl());
        if ($row_currency != "EUR") {
            if (!$this->emptyCheck($response_data['rates'][$row_currency])) {
                throw new RateUrlDataFormatException();
            }
            $rate = $response_data['rates'][$row_currency];
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
     * @throws BinCheckUrlDataFormatException
     * @throws GuzzleException
     * @throws RateUrlDataFormatException
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

    /**
     * Run this app.
     *
     * @param array $inputs
     * @return void
     */
    public function calculate($inputs)
    {
        try {
            $validator = new CalculateCommissionRequest();
            if ($validator->validateInput($inputs)) {
                $this->setAllData($inputs);
                if ($this->checkFileExistence()) {
                    $data = $this->calculateData($this->readFile());
                    $this->response(200, "Data successfully fetched.", $data);
                }
            } else {
                $this->response(422, "Request param validation error.", $validator->validateInputError());
            }
        } catch (FileNotExistsException $e) {
            $this->response(500, $e->errorMessage());
        } catch (FileDataFormatException $e) {
            $this->response(500, $e->errorMessage());
        } catch (GuzzleException $e) {
            $this->response(500, "Exception : " . $e->getMessage());
        } catch (RateUrlDataFormatException $e) {
            $this->response(500, $e->errorMessage());
        } catch (BinCheckUrlDataFormatException $e) {
            $this->response(500, $e->errorMessage());
        } catch (\Exception $e) {
            $this->response(500, "Exception : " . $e->getMessage());
        }
    }
}
