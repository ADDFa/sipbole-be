<?php

namespace App\Http;

class Response
{
    static public function result(mixed $data, int $status = 200)
    {
        return response()->json($data, $status);
    }

    static public function message(string $message, int $status = 400)
    {
        return response()->json(["message" => $message], $status);
    }

    static public function errors(mixed $errors, int $status = 400)
    {
        return response()->json(["errors" => $errors], $status);
    }
}
