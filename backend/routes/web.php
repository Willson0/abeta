<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AnalyticController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CalendlyController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VentureController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\WebinarController;
use App\Http\Middleware\CheckAdmin;
use App\Http\Middleware\CheckTelegram;
use App\Http\Middleware\LoggingMiddleware;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return 123;
});

Route::group (["prefix" => "api"], function () {
    Route::group (["prefix" => "profile", "middleware" => CheckTelegram::class], function () {
       Route::post("/", [UserController::class, "profile"]);
    });

    Route::group (["prefix" => "auth", "middleware" => CheckTelegram::class], function () {
        Route::post("/", [AuthController::class, "store"]);
    });

    Route::group (["prefix" => "webhook"], function () {
        Route::post("/tg", [WebhookController::class, "tgmessage"]);
        Route::get("/calendly", [CalendlyController::class, "callback"]);
    });

    Route::group(["prefix" => "webinar"], function () {
        Route::post("/{id}", [WebinarController::class, "show"]);
        Route::post("/{id}/registration", [WebinarController::class, "registration"])
            ->middleware(CheckTelegram::class);
        Route::post("/{id}/calendar", [WebinarController::class, "calendar"])
            ->middleware(CheckTelegram::class);
    });

    Route::group(["prefix" => "analytic"], function () {
        Route::post("/{id}", [AnalyticController::class, "show"]);
        Route::post("/{id}/getaccess", [AnalyticController::class, "getAccess"])
            ->middleware(CheckTelegram::class);
    });

    Route::group(["prefix" => "feed"], function () {
        Route::get("all", [FeedController::class, "all"]);
        Route::get("analytics", [FeedController::class, "analytics"]);
        Route::get("webinars", [FeedController::class, "webinars"]);
    });

    Route::group(["prefix" => "venture", "middleware" => CheckTelegram::class], function () {
        Route::post("/", [VentureController::class, "store"]);
        Route::post("/status", [VentureController::class, "status"]);
    });

    Route::group(["prefix" => "admin", "middleware" => CheckAdmin::class], function () {
       Route::post("/settings", [SettingsController::class, "settings"]);
       Route::post("/mailing", [AdminController::class, "mailing"]);
    });

    Route::post("/service/{id}/registration", [ServiceController::class, "registration"])->middleware(CheckTelegram::class);

    Route::post("/group", [GroupController::class, "index"])->middleware(CheckTelegram::class);
    Route::post("/support", [SupportController::class, "store"])->middleware(CheckTelegram::class);

    Route::get("test", [AuthController::class, "test"]);
});
