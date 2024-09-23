<?php

namespace App\Services;

class FonnteResponse
{
    private $curl;

    public function __construct($curl)
    {
        $this->curl = $curl;
    }

    public function status(): bool
    {
        return curl_errno($this->curl) ? false : true;
    }

    public function errorMessage()
    {
        return curl_errno($this->curl);
    }
}
