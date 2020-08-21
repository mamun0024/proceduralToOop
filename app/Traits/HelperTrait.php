<?php

namespace App\Traits;

trait HelperTrait
{
    /**
     * Given value emptiness check.
     *
     * @param string $value
     * @return bool
     */
    public function emptyCheck($value)
    {
        if (isset($value) && ($value != null) && ($value != '')) {
            $status = true;
        } else {
            $status = false;
        }
        return $status;
    }
}
