<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnalyticStoreRequest;
use App\Models\Analytic;
use App\Models\AnalyticUser;
use App\Models\User;
use App\Models\UserWebinar;
use App\Models\Webinar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AnalyticController extends Controller
{
    public function index (Request $request) {
        return utils::index(Analytic::class, $request);
    }

    public function store (AnalyticStoreRequest $request) {
        $data = $request->validated();

        $ext = $data["image"]->getClientOriginalExtension();
        $time = time();
        $newFile = "analytics/images/analytics_" . $time . ".$ext";
        Storage::disk("public")->putFileAs("analytics/images", $data["image"], "analytics_" . $time . ".$ext");

        $web = Analytic::create([
            "title" => $data["title"],
            "description" => $data["description"],
            "image" => $newFile,
            "fields" => json_encode($data["fields"]),
            "locked" => 1,
        ]);

        if (isset($data["link"])) $web->link = $data["link"];

        if (isset($data["pdf"])) {
            $pdf = "analytics/pdf/analytics_" . $time . ".pdf";
            Storage::disk("public")->putFileAs("analytics/pdf", $data["pdf"], "analytics_" . $time . ".pdf");

            $web->pdf = $pdf;
        }
        $web->save();

        return response()->json($web);
    }

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

    public function update (Analytic $analytic, AnalyticStoreRequest $request) {
        $data = $request->validated();
        Storage::disk("public")->delete($analytic["image"]);

        $ext = $request->file("image")->extension();
        $time = time();
        $newFile = "analytics/images/analytics_" . $time . ".$ext";
        Storage::disk("public")->putFileAs("analytics/images", $data["image"], "analytics_" . $time . ".$ext");

        $analytic->update([
            "title" => $data["title"],
            "description" => $data["description"],
            "image" => $newFile,
            "fields" => json_encode($data["fields"]),
        ]);

        if (isset($data["link"])) $analytic->link = $data["link"];
        if (isset($data["pdf"])) {
            if (isset($analytic->pdf)) Storage::disk("public")->delete($analytic["pdf"]);
            $pdf = "analytics/pdf/analytics_" . $time . ".pdf";
            Storage::disk("public")->putFileAs("analytics/pdf", $data["pdf"], "analytics_" . $time . ".pdf");

            $analytic->pdf = $pdf;
        }
        $analytic->save();

        return response()->json($analytic);
    }

    public function destroy ($id) {
        Analytic::find($id)->delete();
        return response()->json($id);
    }
}
