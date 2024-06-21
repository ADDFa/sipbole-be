<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::controller(AuthController::class)->group(function () {
    Route::post("sign-in", "signIn");
    Route::patch("refresh", "refresh");
});

Route::middleware("jwt.verify")->group(function () {
    Route::get("/protected", function () {
        return response()->json(["message" => "middleware success"]);
    });
});
