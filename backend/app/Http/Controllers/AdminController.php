<?php

namespace App\Http\Controllers;

use App\Http\Requests\MailingRequest;
use App\Models\User;
use App\Models\Webinar;
use Illuminate\Http\Request;

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
}
