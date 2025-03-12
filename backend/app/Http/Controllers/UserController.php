<?php

namespace App\Http\Controllers;

use App\Models\Support;
use App\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    use SoftDeletes;
    public function profile (Request $request) {
        $user = User::where("telegram_id", $request["initData"]["user"]["id"])->first();

        if (!$user) abort (404);

        if (Support::where("user_id", $user["id"])->exists()) $user["support"] = true;
        else $user["support"] = false;

        return $user;
    }

    public function index (Request $request) {
        $request->limit = 10;
        return utils::index(User::class, $request);
    }
}
