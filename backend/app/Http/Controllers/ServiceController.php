<?php

namespace App\Http\Controllers;

use App\Http\Requests\ServiceStoreReqeust;
use App\Models\Analytic;
use App\Models\Service;
use App\Models\User;
use App\Models\UserService;
use App\Models\UserWebinar;
use App\Models\Webinar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ServiceController extends Controller
{
    public function show ($id, Request $request) {
        $service = Service::find($id);
        return response()->json($service);
    }

//    public function index (Request $request) {
//        $services = Service::all();
//        if (isset($request["initData"])) {
//            if (!utils::isSafe(env("TELEGRAM_BOT_TOKEN"), $request["initData"]))
//                abort(401);
//
//            $decodedData = urldecode($request["initData"]);
//            parse_str($decodedData, $data);
//            $data["user"] = json_decode($data["user"], true);
//
//            $user = User::where("telegram_id", $data["user"]["id"])->first();
//            foreach ($services as $service)
//                if (UserService::where("service_id", $service->id)->where("user_id", $user->id)->exists())
//                    $service["registered"] = true;
//        }
//
//        return response()->json($services);
//    }

    public function registration ($id, Request $request) {
        $user = User::where("telegram_id", $request["initData"]["user"]["id"])->first();;
        $service = Service::find($id);

        if (UserService::where("user_id", $user->id)->where("service_id", $service->id)->exists())
            abort (409, "Вы уже подавали заявку на регистрацию");

        UserService::create([
            "user_id" => $user->id,
            "service_id" => $service->id,
        ]);

        return response()->json($service);
    }

    public function index (Request $request) {
        return response()->json(utils::index(Service::class, $request));
    }

    public function store (ServiceStoreReqeust $request) {
        $data = $request->validated();

        $ext = $data["image"]->getClientOriginalExtension();
        $time = time();
        $newFile = "services/images/services_" . $time . ".$ext";
        Storage::disk("public")->putFileAs("services/images", $data["image"], "services_" . $time . ".$ext");

        $web = Service::create([
            "title" => $data["title"],
            "description" => $data["description"],
            "overview" => $data["overview"],
            "button" => $data["button"],
            "color" => $data["color"],
            "link" => $data["link"],
            "image" => $newFile,
        ]);

        return response()->json($web);
    }

    public function update (Service $service, ServiceStoreReqeust $request) {
        $data = $request->validated();

        Storage::disk("public")->delete($service->image);
        $ext = $request->file("image")->extension();
        $time = time();
        $newFile = "services/images/services_" . $time . ".$ext";
        Storage::disk("public")->putFileAs("services/images", $data["image"], "services_" . $time . ".$ext");

        $service->update([
            "title" => $data["title"],
            "description" => $data["description"],
            "overview" => $data["overview"],
            "button" => $data["button"],
            "color" => $data["color"],
            "link" => $data["link"],
            "image" => $newFile,
        ]);

        return response()->json($service);
    }

    public function destroy ($id) {
        Service::find($id)->delete();
        return response()->json($id);
    }
}
