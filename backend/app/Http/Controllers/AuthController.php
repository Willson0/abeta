<?php

namespace App\Http\Controllers;

use App\Http\Requests\authStoreRequest;
use App\Http\Requests\AuthSubscribeRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class AuthController extends Controller
{
    public function store (authStoreRequest $request) {
        $user = User::where("telegram_id", $request["initData"]["user"]["id"])->first();

        $user->update($request->validated());

        return response()->json($user);
    }

    public function test (Request $request) {
        $cookie = Cookie::forever("admin", "somecookie");
        return response()->json(123)->withCookie($cookie);
    }

    public function subscribe (AuthSubscribeRequest $request) {
        $user = User::where("telegram_id", $request["initData"]["user"]["id"])->first();
        $data = $request->validated();

        $uni = new UnisenderApi(env("UNISENDER_API"));

        $fields = [
            "email" => $data["email"],
            "Name" => $data["name"],
        ];

        $response = $uni->subscribe([
            "list_ids" => (string) env("UNISENDER_LIST_ID"),
            "fields" => $fields,
            "double_optin" => 3,
            "tags" => (string) "Рассылка",
        ]);

        $user->expert_mailing = 1;
        $user->save();

        return response()->json($response);
    }
}
