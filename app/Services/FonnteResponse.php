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
        return !curl_errno($this->curl);
    }

    public function errorMessage()
    {
        return curl_error($this->curl);
    }
}
