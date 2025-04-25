<?php

namespace App\Http\Controllers;

use App\Models\Support;
use App\Models\User;
use App\Models\VentureDeal;
use Carbon\Carbon;
use Illuminate\Http\Request;

class VentureController extends Controller
{
    public function store(Request $request) {
        $user = User::where("telegram_id", $request["initData"]["user"]["id"])->first();

        if (VentureDeal::where("user_id", $user->id)->where("processed", 0)->exists()) abort(400,"Заявка уже была отправлена");
        $venture = VentureDeal::create([
            "user_id" => $user->id,
        ]);

        utils::sendAdmin("🔔 | Новая заявка на *венчурные сделки*!\n\nИнформация о пользователе:\nИмя: {$user->fullname}\nТелефон: {$user->phone}\nТелеграм ID: {$user->telegram_id}\n@{$user->username}");

        return response()->json($venture);
    }

    public function status (Request $request) {
        $user = User::where("telegram_id", $request["initData"]["user"]["id"])->first();

        // 1 - можно подавать заявку. 0 - не закрыта старая; -1 - идет кд
        $status = 1;
        if (VentureDeal::where("user_id", $user->id)->where("processed", 0)->exists()) $status = 0;
        else if (VentureDeal::where("user_id", $user->id)->where("processed", 1)->exists()) {
            $status = 1;

            $sup = VentureDeal::where("user_id", $user->id)->where("processed", 1)->latest()->first();
//            if (Carbon::parse($sup->created_at) > Carbon::now()->subDay()) $status = -1;
            if (Carbon::parse($sup->created_at) > Carbon::now()) $status = -1;
        } else $status = 1;

        return response()->json($status);
    }
}
