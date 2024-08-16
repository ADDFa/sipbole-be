<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\ActivityReportController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BoatController;
use App\Http\Controllers\CredentialController;
use App\Http\Controllers\InfoController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WarrantController;
use App\Http\Controllers\WarrantsBoatController;
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
        Route::controller(InfoController::class)->group(function () {
            Route::get("info", "index");
        });

        Route::controller(BoatController::class)->group(function () {
            Route::get("boat", "index");
            Route::get("boat/{boat}", "show");
            Route::post("boat", "store");
            Route::put("boat/{boat}", "update");
            Route::delete("boat/{boat}", "destroy");
        });

        Route::controller(UserController::class)->group(function () {
            Route::get("user", "index");
            Route::post("user", "store");
            Route::put("user/{user}", "update");
            Route::delete("user/{user}", "destroy");
        });

        Route::controller(WarrantController::class)->group(function () {
            Route::get("warrant/{warrant}", "show");
            Route::post("warrant", "store");
            Route::put("warrant/{warrant}", "update");
            Route::delete("warrant/{warrant}", "destroy");
        });

        Route::controller(ActivityReportController::class)->group(function () {
            Route::get("activity-report", "index");
        });

        Route::controller(ReportController::class)->group(function () {
            Route::post("report-sar", "storeSar");
        });
    });

    Route::controller(UserController::class)->group(function () {
        Route::get("user/{user}", "show");
        Route::put("update-profile/{user}", "updateProfile");
        Route::put("update-profile-pic/{user}", "updateProfilePic");
    });

    Route::controller(CredentialController::class)->group(function () {
        Route::put("update-password", "updatePassword");
    });

    Route::controller(WarrantController::class)->group(function () {
        Route::get("warrant", "index");
    });

    Route::controller(WarrantsBoatController::class)->group(function () {
        Route::patch("warrant-boat/{warrantsBoat}/read", "read");
    });

    Route::controller(ActivityController::class)->group(function () {
        Route::get("activity", "index");
    });

    Route::controller(ReportController::class)->group(function () {
        Route::get("report", "index");
        Route::post("report", "store");
    });
});
