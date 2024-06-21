<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BoatController;
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
    Route::middleware("admin")->group(function () {
        Route::controller(BoatController::class)->group(function () {
            Route::get("boat", "index");
            Route::get("boat/{boat}", "show");
            Route::post("boat", "store");
            Route::put("boat/{boat}", "update");
            Route::delete("boat/{boat}", "destroy");
        });
    });
});
