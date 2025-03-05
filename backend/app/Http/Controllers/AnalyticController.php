<?php

namespace App\Http\Controllers;

use App\Models\Analytic;
use App\Models\AnalyticUser;
use App\Models\User;
use App\Models\UserWebinar;
use Illuminate\Http\Request;

class AnalyticController extends Controller
{
    public function show ($id, Request $request) {
        $analytic = Analytic::find($id);

        if (isset($request["initData"])) {
            if (!utils::isSafe(env("TELEGRAM_BOT_TOKEN"), $request["initData"]))
                abort(401);

            $decodedData = urldecode($request["initData"]);
            parse_str($decodedData, $data);
            $data["user"] = json_decode($data["user"], true);

            $user = User::where("telegram_id", $data["user"]["id"])->first();
            if (AnalyticUser::where("analytic_id", $id)->where("user_id", $user->id)->exists())
                $analytic["locked"] = 0;
        }

        if ($analytic->locked == true) {
            $analytic->description = substr($analytic->description, 0, 100) . "...";
            unset($analytic->pdf);
        }

        return response()->json($analytic);
    }

    public function getAccess($id, Request $request) {
        $analytic = Analytic::find($id);
        $user = User::where("telegram_id", $request["initData"]["user"]["id"])->first();

        if (!$analytic->locked) abort (400, "Аналитика в свободном доступе");
        if (AnalyticUser::where("analytic_id", $analytic->id)->where("user_id", $user->id)->exists())
            abort (400, "Доступ уже был получен.");

        $analyticUser = AnalyticUser::create([
            "analytic_id" => $analytic->id,
            "user_id" => $user->id,
            "data" => json_encode($request->data),
        ]);

        $analytic->locked = 0;
        return response()->json($analytic);
    }
}
