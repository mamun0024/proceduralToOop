<?php

namespace Oop;

use GuzzleHttp\Exception\GuzzleException;
use Oop\CustomException\FileNotExistsException;
use Oop\CustomException\BinCheckUrlDataFormatException;
use Oop\CustomException\RateUrlDataFormatException;
use Oop\Traits\HelperTrait;
use Oop\Traits\ResponseTrait;
use SplFileObject;
use Oop\Requests\CalculateCommissionRequest;

class CalculateCommission
{
    use ResponseTrait;
    use HelperTrait;

    private $settings;

    public function __construct($settings = [])
    {
        $this->settings = $settings['settings'];
    }

    /**
     * Checked the provided country
     * code included in Europe Union
     *
     * @param string $country_code Country code.
     * @return boolean
     */
    private function isEuropeUnion($country_code)
    {
        switch ($country_code) {
            case 'AT':
            case 'BE':
            case 'BG':
            case 'CY':
            case 'CZ':
            case 'DE':
            case 'DK':
            case 'EE':
            case 'ES':
            case 'FI':
            case 'FR':
            case 'GR':
            case 'HR':
            case 'HU':
            case 'IE':
            case 'IT':
            case 'LT':
            case 'LU':
            case 'LV':
            case 'MT':
            case 'NL':
            case 'PO':
            case 'PT':
            case 'RO':
            case 'SE':
            case 'SI':
            case 'SK':
                return true;
            default:
                return false;
        }
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
    private function outputCurrency($value, $from, $to)
    {
        if ($from === $to) {
            $amount = $value;
        } else {
            $amount = $value * $this->callRateUrl($to);
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
    private function ceiling($value, $precision = 0)
    {
        return ceil($value * pow(10, $precision)) / pow(10, $precision);
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
    private function callBinCheckUrl($bin)
    {
        $http_base_url = $this->settings['bin_check_url'];
        $http_endpoint = $bin;
        $http_data     = [];

        // Call api.
        $http_response  = $this->httpRequest(
            $http_base_url,
            $http_endpoint,
            $http_data,
        );
        $http_response_data = json_decode($http_response->getBody()->getContents(), true);

        if (!$this->emptyCheck($http_response_data['country']['alpha2'])) {
            throw new BinCheckUrlDataFormatException();
        } else {
            return $http_response_data['country']['alpha2'];
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
    private function callRateUrl($row_currency)
    {
        $http_base_url = $this->settings['rate_url'];
        $http_endpoint = $this->settings['rate_url'];
        $http_data     = [];

        // Call api.
        $http_response  = $this->httpRequest(
            $http_base_url,
            $http_endpoint,
            $http_data,
        );
        $http_response_data = @json_decode($http_response->getBody()->getContents(), true);

        if ($row_currency != "EUR") {
            if (!$this->emptyCheck($http_response_data['rates'][$row_currency])) {
                throw new RateUrlDataFormatException();
            }
            $rate = $http_response_data['rates'][$row_currency];
        } else {
            $rate = 0;
        }
        return $rate;
    }

    /**
     * Calculate the
     * main data.
     *
     * @param array $rowData
     *
     * @return float
     * @throws BinCheckUrlDataFormatException
     * @throws GuzzleException
     * @throws RateUrlDataFormatException
     */
    private function calculateData($rowData)
    {
        $country_code = $this->callBinCheckUrl($rowData['bin']);
        $rate         = $this->callRateUrl($rowData['currency']);

        if ($rate == 0) {
            $amount = $rowData['amount'];
        } else {
            $amount = $rowData['amount'] / $rate;
        }

        $commission = $this->outputCurrency(
            $amount * ($this->isEuropeUnion($country_code) ? 0.01 : 0.02),
            "EUR",
            $this->settings['currency']
        );

        $ceil_with_precision = $this->ceiling($commission, 2);
        return number_format($ceil_with_precision, 2, '.', '');
    }

    /**
     * Read the input file and
     * fetch the data from the
     * file.
     *
     * @return void|mixed
     * @throws BinCheckUrlDataFormatException
     * @throws FileNotExistsException
     * @throws GuzzleException
     * @throws RateUrlDataFormatException
     */
    private function getDataFromFile()
    {
        $data = [];
        if (!file_exists("././" . $this->settings['file_name'])) {
            throw new FileNotExistsException($this->settings['file_name']);
        } else {
            $file = new SplFileObject("././" . $this->settings['file_name']);
            while (!$file->eof()) {
                $data[] = $this->calculateData(json_decode($file->fgets(), true));
            }
            return $data;
        }
    }

    /**
     * Run this app.
     *
     * @return void
     */
    public function run()
    {
        try {
            $validator = new CalculateCommissionRequest();
            if ($validator->validateInput($this->settings)) {
                $data = $this->getDataFromFile();
                $this->response(200, "Data successfully fetched.", $data);
            } else {
                $this->response(422, "Request param validation error.", $validator->validateInputError());
            }
        } catch (FileNotExistsException $e) {
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
