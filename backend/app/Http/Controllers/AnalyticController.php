<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnalyticStoreRequest;
use App\Models\AdminCookie;
use App\Models\Analytic;
use App\Models\AnalyticUser;
use App\Models\User;
use App\Models\UserWebinar;
use App\Models\Webinar;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
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

            $records = UserWebinar::where('user_id', $user->id)->get();
            $foundFields = [];

            foreach ($records as $record) {
                $data = json_decode($record->data, true);

                if (is_array($data)) {
                    $foundFields = array_merge($foundFields, array_keys($data));
                }
            }

            $records = AnalyticUser::where('user_id', $user->id)->get();

            foreach ($records as $record) {
                $data = json_decode($record->data, true);

                if (is_array($data)) {
                    $foundFields = array_merge($foundFields, array_keys($data));
                }
            }
            $foundFields[] = "Имя";
            if ($user->phone) $foundFields[] = "Телефон";

            $analytic->fields = json_encode(array_values(array_diff(json_decode($analytic->fields, true), $foundFields)));
            if (sizeof(array_diff(json_decode($analytic->fields, true), $foundFields)) == 0) $analytic->locked = 0;
        }

        $cookieparam = Cookie::get("admin");
        if ($cookieparam) {
            $cookie = AdminCookie::where("cookie", $cookieparam)->first();
            if ($cookie) {
                $admin = $cookie->user;
                if ($admin) $analytic["locked"] = 0;
            }
        }

        if ($analytic->locked) {
            $analytic->description = mb_substr($analytic->description, 0, 100) . "...";
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

        if (isset($request->data["Почта"])) {
            $uni = new UnisenderApi(env("UNISENDER_API"));
            $data = $request->data;

            $fields = [
                "email" => $data["Почта"],
                "telegram" => "@" . $user->telegram_id,
                "phone" => $data["Телефон"] ?? null,
                "Name" => $data["Имя"] ?? null,
            ];
            unset($data["Телефон"], $data["Почта"], $data["Имя"]);
            foreach ($data as $key => $el) {
                $fields[utils::transliterate($key)] = $el;
            }

            $apiResponse = json_decode($uni->getFields(), true);
            $existingNames = array_column($apiResponse["result"], 'name');

            foreach ($data as $key => $value) {
                $translated = utils::transliterate($key);
                if (!in_array($translated, $existingNames)) {
                    $resp = $uni->createField([
                        "name" => $translated,
                        "type" => "string",
                        "public_name" => $key
                    ]);
                }
            }

            $response = $uni->subscribe([
                "list_ids" => (string) env("UNISENDER_LIST_ID"),
                "fields" => $fields,
                "double_optin" => 3,
                "tags" => (string) $analytic->title,
            ]);
        }

        $analytic->locked = 0;
        return response()->json($analytic);
    }

    public function update (Analytic $analytic, AnalyticStoreRequest $request) {
        Log::critical($request->all());
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
