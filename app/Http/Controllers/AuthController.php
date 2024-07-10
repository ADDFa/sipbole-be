<?php

namespace App\Http\Controllers;

use App\Http\Response;
use App\Models\Credential;
use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function signIn(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "username"  => "required|string",
            "password"  => "required|string"
        ]);
        if ($validator->fails()) return Response::errors($validator->errors());

        // get auth data
        $auth = Credential::where(DB::raw("BINARY `username`"), $request->username)->first();
        if (!$auth) return Response::message("Username atau Password salah!");

        // check password
        $isCorrectPass = password_verify($request->password, $auth->password);
        if (!$isCorrectPass) return Response::message("Username atau Password salah!");

        // generate token
        $tokens = $this->generateToken($auth);
        return Response::result($tokens);
    }

    public function refresh(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "token" => "required|string"
        ]);
        if ($validator->fails()) return Response::errors($validator->errors());

        try {
            $refresh = Config::get("jwt.refresh");
            $algo = Config::get("jwt.algo");
            $payload = JWT::decode($request->token, new Key($refresh, $algo));
            $user = User::find($payload->id);
            $credential = Credential::find($user->credential_id);

            return $this->generateToken($credential);
        } catch (\Exception $e) {
            return Response::message("invalid token", 401);
        }
    }

    public static function generateToken(Credential $auth): array
    {
        $secret = Config::get("jwt.secret");
        $refresh = Config::get("jwt.refresh");
        $algo = Config::get("jwt.algo");
        $result = [];

        $time = time();
        $userId = User::where("credential_id", $auth->id)->value("id");

        $payload = [
            "role"      => $auth->role,
            "id"        => $userId,
            "auth_id"   => $auth->id,
            "iat"       => $time // Time when JWT was issued
        ];
        $result["payload"] = $payload;

        $payload["exp"] = $time + Config::get("jwt.exp_access");
        $accessToken = JWT::encode($payload, $secret, $algo);
        $result["access_token"] = $accessToken;

        $payload["exp"] = $time + Config::get("jwt.exp_refresh");
        $refreshToken = JWT::encode($payload, $refresh, $algo);
        $result["refresh_token"] = $refreshToken;

        return $result;
    }
}
