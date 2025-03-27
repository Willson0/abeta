<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AnalyticController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CalendlyController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\MailingController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VentureController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\WebinarController;
use App\Http\Middleware\CheckAdmin;
use App\Http\Middleware\CheckAdminMiddleware;
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
        Route::get("/google", [GoogleController::class, "callback"]);
    });

    Route::group (["prefix" => "google"], function () {
       Route::post("link", [GoogleController::class, "getLink"])->middleware(CheckTelegram::class);
    });

    Route::group(["prefix" => "webinar"], function () {
        Route::get("/", [WebinarController::class, "index"]);
        Route::post("/", [WebinarController::class, "store"]);
        Route::post("/{id}", [WebinarController::class, "show"]);
        Route::post("/{webinar}/edit", [WebinarController::class, "update"])->middleware(CheckAdminMiddleware::class);
        Route::post("/{id}/registration", [WebinarController::class, "registration"])
            ->middleware(CheckTelegram::class);
        Route::post("/{id}/calendar", [WebinarController::class, "calendar"])
            ->middleware(CheckTelegram::class);
    });

    Route::group(["prefix" => "analytic"], function () {
        Route::get("/", [AnalyticController::class, "index"]);
        Route::post("/", [AnalyticController::class, "store"])->middleware(CheckAdminMiddleware::class);
        Route::post("/{analytic}/edit", [AnalyticController::class, "update"])->middleware(CheckAdminMiddleware::class);
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

    Route::post("/admin/login", [AdminController::class, "login"]);
    Route::group(["prefix" => "admin", "middleware" => CheckAdminMiddleware::class], function () {
       Route::get("profile", [AdminController::class, "profile"]);
        Route::get("/fields", [AdminController::class, "fields"]);
        Route::post("/getfile", [AdminController::class, "getFile"]);
    });

    Route::group(["prefix" => "stats", "middleware" => CheckAdminMiddleware::class], function () {
       Route::get("/", [StatsController::class, "index"]);
    });

    Route::post("/service/{id}/registration", [ServiceController::class, "registration"])->middleware(CheckTelegram::class);
    Route::group(["prefix" => "service", "middleware" => CheckAdminMiddleware::class], function () {
        Route::get("/", [ServiceController::class, "index"]);
        Route::post("/", [ServiceController::class, "store"]);
        Route::post("/{id}", [ServiceController::class, "show"]);
        Route::post("/{service}/edit", [ServiceController::class, "update"]);
    });

    Route::group(["prefix" => "user", "middleware" => CheckAdminMiddleware::class], function () {
       Route::get("/", [UserController::class, "index"]);
    });

     Route::group(["prefix" => "mailing", "middleware" => CheckAdminMiddleware::class], function () {
         Route::post("/", [MailingController::class, "send"]);
         Route::post("/all", [MailingController::class, "sendAll"]);
     });

    Route::post("/group", [GroupController::class, "index"])->middleware(CheckTelegram::class);
    Route::post("/support", [SupportController::class, "store"])->middleware(CheckTelegram::class);

    Route::get("test", [AuthController::class, "test"]);
});
