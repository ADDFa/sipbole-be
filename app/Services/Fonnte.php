<?php

namespace App\Services;

class Fonnte
{
    private $url = "https://api.fonnte.com/send";
    private $curl;
    private $token;

    public function __construct($token)
    {
        $this->curl = curl_init();
        $this->token = $token;
    }

    public function sendMessage(string $target, string $message): FonnteResponse
    {
        curl_setopt_array($this->curl, [
            CURLOPT_URL             => $this->url,
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_ENCODING        => "",
            CURLOPT_MAXREDIRS       => 10,
            CURLOPT_TIMEOUT         => 0,
            CURLOPT_FOLLOWLOCATION  => true,
            CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST   => "POST",
            CURLOPT_POSTFIELDS      => [
                "target"        => $target,
                "message"       => $message,
                "schedule"      => 0,
                "typing"        => false,
                "delay"         => "0",
                "countryCode"   => "62"
            ],
            CURLOPT_HTTPHEADER      => [
                "Authorization: {$this->token}"
            ]
        ]);

        curl_exec($this->curl);
        return new FonnteResponse($this->curl);
    }
}
