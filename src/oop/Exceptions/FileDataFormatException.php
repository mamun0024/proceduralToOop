<?php

namespace Oop\Exceptions;

class FileDataFormatException extends BaseException
{
    public function errorMessage()
    {
        //error message
        $format = ' File data format is not as expected.';
        return $this->errorMessageFormat($format);
    }
}
