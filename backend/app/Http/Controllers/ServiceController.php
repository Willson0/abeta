<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\User;
use App\Models\UserService;
use App\Models\UserWebinar;
use App\Models\Webinar;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function show ($id, Request $request) {

    }

    public function index (Request $request) {
        $services = Service::all();
        if (isset($request["initData"])) {
            if (!utils::isSafe(env("TELEGRAM_BOT_TOKEN"), $request["initData"]))
                abort(401);

            $decodedData = urldecode($request["initData"]);
            parse_str($decodedData, $data);
            $data["user"] = json_decode($data["user"], true);

            $user = User::where("telegram_id", $data["user"]["id"])->first();
            foreach ($services as $service)
                if (UserService::where("service_id", $service->id)->where("user_id", $user->id)->exists())
                    $service["registered"] = true;
        }

        return response()->json($services);
    }

    public function registration ($id, Request $request) {
        $user = User::where("telegram_id", $request["initData"]["user"]["id"])->first();;
        $service = Service::find($id);

        if (UserService::where("user_id", $user->id)->where("service_id", $service->id)->exists())
            abort (409, "Вы уже подавали заявку на регистрацию");

        UserService::create([
            "user_id" => $user->id,
            "webinar_id" => $service->id,
        ]);

        $service["registered"] = true;

        return response()->json($service);
    }
}
