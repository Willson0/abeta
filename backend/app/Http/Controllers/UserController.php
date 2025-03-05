<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    use SoftDeletes;
    public function profile (Request $request) {
        $user = User::where("telegram_id", $request["initData"]["user"]["id"])->first();

        Log::critical($user);

        return $user;
    }
}
