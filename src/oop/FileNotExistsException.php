<?php

namespace Oop;

class FileNotExistsException extends BaseException
{
    public function errorMessage()
    {
        //error message
        $format = $this->getMessage() . ' file is not exists.';
        return $this->errorMessageFormat($format);
    }
}
