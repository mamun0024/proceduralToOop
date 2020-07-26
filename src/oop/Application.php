<?php

namespace Oop;

use GuzzleHttp\Exception\GuzzleException;
use Oop\Exceptions\BinCheckUrlDataFormatException;
use Oop\Exceptions\FileDataFormatException;
use Oop\Exceptions\FileNotExistsException;
use Oop\Exceptions\RateUrlDataFormatException;
use Oop\Requests\ApplicationRequest;
use Oop\Traits\ResponseTrait;

class Application
{
    use ResponseTrait;

    private $settings;

    public function __construct($settings = [])
    {
        $this->settings = $settings['settings'];
    }

    /**
     * Run the app.
     *
     * @return mixed
     */
    public function run()
    {
        try {
            $validator = new ApplicationRequest();
            if ($validator->validateInput($this->settings)) {
                $comm_file = new CommissionFile(
                    $this->settings['file_name'],
                    $this->settings['file_path']
                );
                $comm_file->checkFileExistence();
                $comm_file_data = $comm_file->readFile();

                $comm_cal = new CommissionCalculation(
                    $this->settings['bin_check_url'],
                    $this->settings['rate_url'],
                    $this->settings['currency'],
                    $this->settings['eu_commission'],
                    $this->settings['ex_eu_commission'],
                    new CountryCodeFormat(),
                    new RateFormat()
                );
                $final_result = $comm_cal->calculateData($comm_file_data);

                $response = $this->response(200, "Data successfully fetched.", $final_result);
            } else {
                $response = $this->response(422, "Request param validation error.", $validator->validateInputError());
            }
        } catch (FileNotExistsException $e) {
            $response = $this->response(500, $e->errorMessage());
        } catch (FileDataFormatException $e) {
            $response = $this->response(500, $e->errorMessage());
        } catch (GuzzleException $e) {
            $response = $this->response(500, "Exception : " . $e->getMessage());
        } catch (RateUrlDataFormatException $e) {
            $response = $this->response(500, $e->errorMessage());
        } catch (BinCheckUrlDataFormatException $e) {
            $response = $this->response(500, $e->errorMessage());
        } catch (\Exception $e) {
            $response = $this->response(500, "Exception : " . $e->getMessage());
        }

        return $response;
    }
}
