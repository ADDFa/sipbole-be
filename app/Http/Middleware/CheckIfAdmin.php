<?php

namespace App\Http\Middleware;

use App\Http\Response;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class CheckIfAdmin
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
        $subject = $request->get("user");
        $user = User::with("credential")->find($subject);
        $role = $user->credential->role;

        return $role === "admin" ? $next($request) : Response::message("Anda tidak memiliki akses ke konten ini");
    }
}
