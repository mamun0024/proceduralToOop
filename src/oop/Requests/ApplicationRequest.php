<?php

namespace Oop\Requests;

class ApplicationRequest extends Request
{
    private $validationError;

    /**
     * Validate the request data value
     *
     * @param array $data
     * @return boolean
     */
    public function validateInput($data)
    {
        $validation = $this->validate($data, [
            'file_name'        => 'required',
            'bin_check_url'    => 'required',
            'rate_url'         => 'required',
            'currency'         => 'required',
            'eu_commission'    => 'required|numeric',
            'ex_eu_commission' => 'required|numeric'
        ]);

        if ($validation->fails()) {
            $this->validationError = $validation->errors()->firstOfAll();
            return false;
        } else {
            return true;
        }
    }

    /**
     * Response validation error message.
     *
     * @return array
     */
    public function validateInputError()
    {
        return $this->validationError;
    }
}
