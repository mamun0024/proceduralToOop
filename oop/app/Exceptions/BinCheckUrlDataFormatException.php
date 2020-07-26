<?php

namespace App\Exceptions;

class BinCheckUrlDataFormatException extends BaseException
{
    public function errorMessage()
    {
        //error message
        $format = ' Bin url response data format is not as expected.';
        return $this->errorMessageFormat($format);
    }
}
