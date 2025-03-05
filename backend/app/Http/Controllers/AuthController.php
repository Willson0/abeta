<?php

namespace App\Http\Controllers;

use App\Http\Requests\authStoreRequest;
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
}
