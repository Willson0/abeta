<?php

namespace App\Http\Controllers;

use App\Http\Requests\WebinarStoreRequest;
use App\Models\Analytic;
use App\Models\AnalyticUser;
use App\Models\User;
use App\Models\UserWebinar;
use App\Models\Webinar;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\UnisenderApi;

class WebinarController extends Controller
{
    public function index (Request $request) {
        return utils::index(Webinar::class, $request);
    }

    public function show($id, Request $request) {
        $webinar = Webinar::find($id);

        if (!$webinar) abort (404);

        if (isset($request["initData"])) {
            if (!utils::isSafe(env("TELEGRAM_BOT_TOKEN"), $request["initData"]))
                abort(401);

            $decodedData = urldecode($request["initData"]);
            parse_str($decodedData, $data);
            $data["user"] = json_decode($data["user"], true);

            $user = User::where("telegram_id", $data["user"]["id"])->first();
            if (UserWebinar::where("webinar_id", $id)->where("user_id", $user->id)->exists()) {
                $webinar["registered"] = true;
                $webinar["added_calendar"] = UserWebinar::where("webinar_id", $id)->where("user_id", $user->id)->first()->added_calendar;
            }

            $records = array_merge(AnalyticUser::where('user_id', $user->id)->get()->toArray(), UserWebinar::where('user_id', $user->id)->get()->toArray());
            $foundFields = [];

            foreach ($records as $record) {
                $data = json_decode($record->data, true);

                if (is_array($data)) {
                    $foundFields = array_merge($foundFields, array_keys($data));
                }
            }
            $foundFields[] = "Имя";
            if ($user->phone) $foundFields[] = "Телефон";

            $webinar->fields = json_encode(array_values(array_diff(json_decode($webinar->fields, true), $foundFields)));
        }
        if (!$webinar["registered"] && !$request->cookie("admin")) unset($webinar["link"]);

        return response()->json($webinar);
    }

    public function registration($id, Request $request) {
        $user = User::where("telegram_id", $request["initData"]["user"]["id"])->first();;
        $webinar = Webinar::find($id);

        if (UserWebinar::where("user_id", $user->id)->where("webinar_id", $webinar->id)->exists())
            abort (409, "Вы уже подавали заявку на регистрацию");

        $userWebinar = UserWebinar::create([
            "user_id" => $user->id,
            "webinar_id" => $webinar->id,
            "data" => json_encode($request->data),
        ]);

        $webinar["registered"] = true;

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
                "tags" => (string) $webinar->title,
            ]);
        }

        return response()->json($webinar);
    }

    public function calendar($id, Request $request) {
        $user = User::where("telegram_id", $request["initData"]["user"]["id"])->first();;
        $webinar = Webinar::find($id);

        if (!UserWebinar::where("user_id", $user->id)->where("webinar_id", $webinar->id)->exists())
            abort (409, "Вы еще не зарегестрировались в этом вебинаре");

        if ($user->calendly_access_token == null) abort (401, "Аккаунт calendly не привязан");

        $response = Http::withToken($user->calendly_access_token)->get("https://api.calendly.com/users/me");
        if ($response->successful()) {

            $data = $response->json();
            $uri = $data["resource"]["uri"];
            $resp = Http::withToken($user->calendly_access_token)
                ->post("https://api.calendly.com/one_off_event_types", [
                    "name" => substr($webinar->title, 0, 50),
                    "host" => $uri,
                    "location" => [
                        "kind" => "zoom_conference",
                    ],
                    "duration" => 60,
                    "date_setting" => [
                        "type" => "date_range",
                        "start_date" => Carbon::parse($webinar->date)->toDateString(),
                        "end_date" => Carbon::parse($webinar->date)->toDateString()
                    ]
                ]);
        }
    }

    public function store (WebinarStoreRequest $request) {
        $data = $request->validated();

        $ext = $data["image"]->getClientOriginalExtension();
        $time = time();
        $newFile = "webinars/webinar_" . $time . ".$ext";
        Storage::disk("public")->putFileAs("webinars", $data["image"], "webinar_" . $time . ".$ext");

        $fields = [];
        if ($request->has("fields")) $fields = $data["fields"];

        $web = Webinar::create([
            "title" => $data["title"],
            "description" => $data["description"],
            "link" => $data["link"],
            "image" => $newFile,
            "date" => $data["date"],
            "fields" => json_encode($fields),
        ]);

        return response()->json($web);
    }

    public function update (Webinar $webinar, WebinarStoreRequest $request) {
        $data = $request->validated();
        Storage::disk("public")->delete($webinar["image"]);

        $ext = $request->file("image")->extension();
        $time = time();
        $newFile = "webinars/webinar_" . $time . ".$ext";
        Storage::disk("public")->putFileAs("webinars", $data["image"], "webinar_" . $time . ".$ext");

        $fields = [];
        if ($request->has("fields")) $fields = $data["fields"];

        $webinar->update([
            "title" => $data["title"],
            "description" => $data["description"],
            "link" => $data["link"],
            "image" => $newFile,
            "date" => $data["date"],
            "fields" => json_encode($fields),
        ]);

        if (isset($data["record_link"])) $webinar->record_link = $data["record_link"];
        $webinar->save();

        return response()->json($webinar);
    }

    public function destroy ($id) {
        Webinar::find($id)->delete();
        return response()->json($id);
    }
}
