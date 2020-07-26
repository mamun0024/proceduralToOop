<?php

namespace App\Exceptions;

class FileNotExistsException extends BaseException
{
    public function errorMessage()
    {
        //error message
        $format = 'Input file is not exists.';
        return $this->errorMessageFormat($format);
    }
}
