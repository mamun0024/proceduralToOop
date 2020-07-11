<?php

namespace Oop\Traits;

trait ResponseTrait
{
    /**
     * Response body.
     *
     * @param integer $code Status code.
     * @param string $message Response message.
     * @param array $data Response data's details.
     * @return mixed
     */
    public function response($code, $message, $data = null)
    {
        return json_encode([
            'status'  => ($code > 200) ? false : true,
            'code'    => $code,
            'message' => $message,
            'data'    => $data
        ], JSON_PRETTY_PRINT);
    }
}
