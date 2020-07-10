<?php

namespace Oop;

use Exception;

class BaseException extends Exception
{
    protected function errorMessageFormat($custom_text)
    {
        return  'Exception : Error on line <b>' . $this->getLine() . '</b> in <b>'
            . $this->getFile() . '</b> : ' . $custom_text;
    }
}
