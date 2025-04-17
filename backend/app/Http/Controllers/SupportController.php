<?php

namespace App\Http\Controllers;

use App\Models\Support;
use App\Models\User;
use Illuminate\Http\Request;

class SupportController extends Controller
{
    public function store(Request $request) {
        $user = User::where("telegram_id", $request["initData"]["user"]["id"])->first();

        if (Support::where("user_id", $user->id)->where("closed", 0)->exists()) abort (429);
        if ($request->has("text")) $text = $request->text;
        else $text = "Вопрос по теме вебинара/аналитики.";

        $sup = Support::create([
           "user_id" => $user->id,
           "text" => $text,
        ]);

        utils::sendAdmin("🔔 | Новое обращение по *консультации*!");

        return response()->json($sup);
    }
}
