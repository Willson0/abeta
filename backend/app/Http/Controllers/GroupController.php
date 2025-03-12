<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    public function index (Request $request) {
        $user = User::where("telegram_id", $request["initData"]["user"]["id"])->first();
        utils::sendMessage($user->telegram_id, "Ссылка на присоединение в группу:\n" . utils::getSettings()["group_link"]);

        $user->chat_request = 1;
        $user->save();

        return response()->json("ok");
    }
}
