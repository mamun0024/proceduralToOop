<?php

namespace Oop\Exceptions;

class RateUrlDataFormatException extends BaseException
{
    public function errorMessage()
    {
        //error message
        $format = ' Rate url response data format is not as expected.';
        return $this->errorMessageFormat($format);
    }
}
