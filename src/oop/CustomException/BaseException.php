<?php

namespace Oop\CustomException;

use Exception;

class BaseException extends Exception
{
    protected function errorMessageFormat($custom_text)
    {
        return  'Exception : Error on line ' . $this->getLine() . ' in '
            . $this->getFile() . ' : ' . $custom_text;
    }
}
