<?php

namespace App\Http\Controllers;

use App\Http\Requests\MailingRequest;
use App\Models\Admin;
use App\Models\User;
use App\Models\Webinar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    public function mailing (MailingRequest $request) {
        $data = $request->validated();

        if ($data == null) abort (409);

//        unset($data["text"]);
//        foreach ($data as $key => $value) {
//            if ($key == "ids") $data = User::whereIn("id", $value)->get();
//            else if ($key == "webinar_id") $data = Webinar::find($value)->users;
//            else if ($key == "telegram_id") $data = User::where("telegram_id", $value)->get();
//            else $data = User::where($key, "like", "%$value%")->get();
//
//            break;
//        }

        $data = User::whereIn("id", $request->ids)->get();

        foreach ($data as $user) {
            utils::sendMessage($user->telegram_id, $request->text);
        }

        return response()->json(["message" => "Successful"]);
    }

    public function profile (Request $request) {
        return $request->get("user");
    }

    public function login (Request $request) {
        $admin = Admin::where("login", $request->login)->first();
        if (!$admin or !password_verify($request->password, $admin->password))
            abort (403, "Неверный логин или пароль");

        $cookie = utils::gen_cookie($admin, isadmin: true);
        $respcookie = Cookie::forever("admin", $cookie);

        return response()
            ->json(["Message" => "Успешная авторизация!", "cookie" => $cookie])
            ->withCookie($respcookie);
    }

    public function fields (Request $request) {
        return response()->json(utils::getSettings()["webinar_fields"]);
    }

    public function getFile (Request $request) {
        return response(Storage::disk("public")->get($request->path))
            ->header("content-type",  Storage::disk('public')->mimeType($request->path));
    }
}
