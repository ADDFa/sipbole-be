<?php

namespace App\Http\Middleware;

use App\Http\Response;
use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class VerifyJwtToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) return Response::message("Token not provide!", 401);

        try {
            $secret = Config::get("jwt.secret");
            $algo = Config::get("jwt.algo");

            $payload = JWT::decode($token, new Key($secret, $algo));
            $request->attributes->add(["user" => $payload->sub]);

            return $next($request);
        } catch (\Exception $e) {
            return Response::message("invalid token", 401);
        }
    }
}
