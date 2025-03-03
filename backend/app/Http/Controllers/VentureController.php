<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\VentureDeal;
use Illuminate\Http\Request;

class VentureController extends Controller
{
    public function store(Request $request) {
        $user = User::where("telegram_id", $request["initData"]["user"]["id"])->first();

        if (VentureDeal::where("user_id", $user->id)->exists()) abort(400,"Заявка уже была отправлена");
        $venture = VentureDeal::create([
            "user_id" => $user->id,
        ]);

        return response()->json($venture);
    }

    public function status (Request $request) {
        $user = User::where("telegram_id", $request["initData"]["user"]["id"])->first();

        return response()->json(VentureDeal::where("user_id", $user->id)->exists());
    }
}
