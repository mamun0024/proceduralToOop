<?php

namespace Oop;

use Oop\CustomException\FileNotExistsException;
use Oop\Traits\ResponseTrait;
use SplFileObject;
use Rakit\Validation\Validator;

class CalculateCommission
{
    use ResponseTrait;

    private $settings;

    public function __construct($settings = [])
    {
        $this->settings = $settings['settings'];
    }

    /**
     * Read the input file and
     * fetch the data from the
     * file.
     *
     * @return void|mixed
     * @throws FileNotExistsException
     */
    private function getDataFromFile()
    {
        if (!file_exists("././" . $this->settings['file_name'])) {
            throw new FileNotExistsException($this->settings['file_name']);
        } else {
            $file = new SplFileObject("././" . $this->settings['file_name']);
            while (!$file->eof()) {
                echo $file->fgets();
            }
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
            if ($this->validateInput()) {
                $this->getDataFromFile();
            }
        } catch (FileNotExistsException $e) {
            $this->response(
                500,
                $e->errorMessage()
            );
        } catch (\Exception $e) {
            $this->response(
                500,
                "Exception : " . $e->getMessage()
            );
        }
    }

    /**
     * Validate the settings value
     *
     * @return void|boolean
     */
    private function validateInput()
    {
        $validator = new Validator();
        $validation = $validator->validate($this->settings, [
            'file_name'     => 'required',
            'bin_check_url' => 'required',
            'rate_url'      => 'required',
            'currency'      => 'required'
        ]);

        if ($validation->fails()) {
            $this->response(
                422,
                "Request param validation error.",
                $validation->errors()->firstOfAll()
            );
        } else {
            return true;
        }
    }
}
