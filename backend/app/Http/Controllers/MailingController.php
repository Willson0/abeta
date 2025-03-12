<?php

namespace App\Http\Controllers;

use App\Http\Requests\MailingSendRequest;
use App\Models\Analytic;
use App\Models\User;
use App\Models\Webinar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MailingController extends Controller
{
    public function send (MailingSendRequest $request) {
        $data = $request->validated();
        $token = env("TELEGRAM_BOT_TOKEN");

        if (!isset($data["users"])) $data["users"] = [];

        if (isset($data["webinars"]))
        foreach ($data["webinars"] as $web) {
            $webinar = Webinar::find($web);
            if ($webinar->users) $data["users"] = array_merge($data['users'], $webinar->users->pluck("id")->toArray());
        }
        if (isset($data["analytics"]))
        foreach ($data["analytics"] as $web) {
            $webinar = Analytic::find($web);
            if ($webinar->users) $data["users"] = array_merge($data['users'], $webinar->users->pluck("id")->toArray());
        }
        $data["users"] = array_unique($data['users']);
        foreach ($data["users"] as $user) {
            if (!isset($user["id"]))
                $user = User::find($user);
//            if (isset($data["image"]))
//                $resp = Http::attach(
//                    "photo",
//                    $data["image"],
//                    "photo.jpg"
//                )->post("https://api.telegram.org/bot{$token}/sendPhoto", [
//                    "chat_id" => $user["telegram_id"],
//                    "caption" => $data["text"],
//                ]);
//            else
                $resp = Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
                "chat_id" => $user["telegram_id"],
                "text" => $data["text"],
            ]);
            Log::critical($resp);
        }
    }

    public function sendAll (Request $request) {
        $token = env("TELEGRAM_BOT_TOKEN");
        foreach (User::all() as $user) {
            $resp = Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
            "chat_id" => $user["telegram_id"],
            "text" => $request["text"],
        ]);
        }
    }
}
