<?php

namespace Oop;

use GuzzleHttp\Exception\GuzzleException;
use Oop\Exceptions\FileNotExistsException;
use Oop\Exceptions\BinCheckUrlDataFormatException;
use Oop\Exceptions\RateUrlDataFormatException;
use Oop\Traits\HelperTrait;
use Oop\Traits\ResponseTrait;
use SplFileObject;
use Oop\Requests\CalculateCommissionRequest;

class CalculateCommission
{
    use ResponseTrait;
    use HelperTrait;

    private $file_name;
    private $bin_url;
    private $rate_url;
    private $output_currency;

    public function setData($inputs)
    {
        $this->file_name       = $inputs['file_name'];
        $this->bin_url         = $inputs['bin_url'];
        $this->rate_url        = $inputs['rate_url'];
        $this->output_currency = $inputs['currency'];
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
        $response_data = $this->callExternalUrl($this->bin_url, $bin);
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
        $response_data = $this->callExternalUrl($this->rate_url, $this->rate_url);
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
    public function calculateData($rowData)
    {
        $country_code = $this->getCountryCode($rowData['bin']);
        $rate         = $this->getRate($rowData['currency']);

        if ($rate == 0) {
            $amount = $rowData['amount'];
        } else {
            $amount = $rowData['amount'] / $rate;
        }

        $commission = $this->outputCurrency(
            $amount * ($this->isEuropeUnion($country_code) ? 0.01 : 0.02),
            "EUR",
            $this->output_currency
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
    public function getDataFromFile()
    {
        $data = [];
        if (!file_exists("././" . $this->file_name)) {
            throw new FileNotExistsException();
        } else {
            $file = new SplFileObject("././" . $this->file_name);
            while (!$file->eof()) {
                $data[] = $this->calculateData(json_decode($file->fgets(), true));
            }
            return $data;
        }
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
                $this->setData($inputs);
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
