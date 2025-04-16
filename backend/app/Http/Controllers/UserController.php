<?php

namespace App\Http\Controllers;

use App\Models\Support;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    use SoftDeletes;
    public function profile (Request $request) {
        $user = User::where("telegram_id", $request["initData"]["user"]["id"])->first();

        if (!$user) abort (404);

        if (Support::where("user_id", $user["id"])->where("closed", 0)->exists()) {
            $user["support"] = 0;
        } else if (Support::where("user_id", $user["id"])->where("closed", 1)->exists()) {
            $user["support"] = 1;

            $sup = Support::where("user_id", $user["id"])->where("closed", 1)->latest()->first();
            if (Carbon::parse($sup->created_at) > Carbon::now()->subDay()) $user["support"] = -1;
        }
        else $user["support"] = 1;
        // 1 - можно подавать заявку. 0 - не закрыта старая; -1 - идет кд

        return $user;
    }

    public function index (Request $request) {
        $request->limit = 10;
        return utils::index(User::class, $request);
    }
}
