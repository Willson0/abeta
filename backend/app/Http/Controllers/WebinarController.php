<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserWebinar;
use App\Models\Webinar;
use Illuminate\Http\Request;

class WebinarController extends Controller
{
    public function show($id, Request $request) {
        $webinar = Webinar::find($id);

        if (isset($request["initData"])) {
            if (!utils::isSafe(env("TELEGRAM_BOT_TOKEN"), $request["initData"]))
                abort(401);

            $decodedData = urldecode($request["initData"]);
            parse_str($decodedData, $data);
            $data["user"] = json_decode($data["user"], true);

            $user = User::where("telegram_id", $data["user"]["id"])->first();
            if (UserWebinar::where("webinar_id", $id)->where("user_id", $user->id)->exists())
                $webinar["registered"] = true;
        }

        if (!$webinar["registered"]) unset($webinar["link"]);

        return response()->json($webinar);
    }

    public function registration($id, Request $request) {
        $user = User::where("telegram_id", $request["initData"]["user"]["id"])->first();;
        $webinar = Webinar::find($id);

        if (UserWebinar::where("user_id", $user->id)->where("webinar_id", $webinar->id)->exists())
            abort (409, "Вы уже подавали заявку на регистрацию");

        if ($user->fullname != $request->fullname or $user->phone != $request->phone)
            $user->update([
                "fullname" => $request->fullname,
                "phone" => $request->phone,
            ]);

        $userWebinar = UserWebinar::create([
            "user_id" => $user->id,
            "webinar_id" => $webinar->id,
            "fullname" => $request->fullname,
            "phone" => $request->phone,
        ]);

        $webinar["registered"] = true;

        return response()->json($webinar);
    }
}
