<?php

namespace App\Http\Controllers;

use App\Models\User;
use Google\Client;
use Illuminate\Http\Request;

class GoogleController extends Controller
{
    public function getLink (Request $request) {
        $auth_code = utils::gen_str(12);
        $user = User::where("telegram_id", $request["initData"]["user"]["id"])->first();
        $user->auth_code = $auth_code;
        $user->save();

        $client = new \Google_Client();
        $client->setClientId(env("GOOGLE_CLIENT_ID"));
        $client->setClientSecret(env("GOOGLE_CLIENT_SECRET"));
        $client->setRedirectUri(env("GOOGLE_REDIRECT_URI"));
        $client->setState($auth_code);
        $client->addScope('https://www.googleapis.com/auth/calendar');

        return response($client->createAuthUrl());
    }

    public function callback (Request $request) {
        $client = new \Google_Client();
        $client->setClientId(env("GOOGLE_CLIENT_ID"));
        $client->setClientSecret(env("GOOGLE_CLIENT_SECRET"));
        $client->setRedirectUri(env("GOOGLE_REDIRECT_URI"));

        $token = $client->fetchAccessTokenWithAuthCode($request->code);

        if (isset($token['error'])) {
            return response(['error' => 'Google auth failed'], 400);
        }

        $auth_code = $request->state;
        $user = User::where('auth_code', $auth_code)->first();
        $user->google_access_token = $token['access_token'];
        $user->google_refresh_token = $token['refresh_token'] ?? null;
        $user->google_token_expires = now()->addSeconds($token['expires_in']);
        $user->save();

        return view("calendly");
    }
}
