<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CalendlyController extends Controller
{
    public function callback (Request $request) {
        $code = $request->code;

//        if ($request->has("admin")) {
//            $response = Http::asForm()->post('https://auth.calendly.com/oauth/token', [
//                'grant_type'    => 'authorization_code',
//                'client_id'     => env('CALENDLY_CLIENT_ID'),
//                'client_secret' => env('CALENDLY_CLIENT_SECRET'),
//                'redirect_uri'  => env('CALENDLY_CALLBACK') . "?admin=1",
//                'code'          => $code,
//            ]);
//
//            if ($response->successful()) {
//                $data = $response->json();
//
//                utils::updateSettings("calendly_token", $data['access_token']);
//                return response()->json("ok",200);
//            }
//
//            return response()->json("error", 409);
//        }

        $auth_code = $request->auth_code;
        $user = User::where('auth_code', $auth_code)->first();
        if (!$user) abort (404);

        $response = Http::asForm()->post('https://auth.calendly.com/oauth/token', [
            'grant_type'    => 'authorization_code',
            'client_id'     => env('CALENDLY_CLIENT_ID'),
            'client_secret' => env('CALENDLY_CLIENT_SECRET'),
            'redirect_uri'  => env('CALENDLY_CALLBACK') . "?auth_code=" . $auth_code,
            'code'          => $code,
        ]);
        Log::critical($response);
        Log::critical($code);
        Log::critical($auth_code);

        if ($response->successful()) {
            $data = $response->json();

            // Предполагается, что у пользователя в таблице есть поля для хранения токенов

            $user->calendly_access_token  = $data['access_token'];
            $user->calendly_refresh_token = $data['refresh_token'];
            $user->auth_code = null;
            $user->save();

            return view('calendly');
        }

        return response("Произошла ошибка. Не удалось привязать аккаунт, обратитесь к администраторам.", 409);
    }
}
