<?php

namespace App\Http\Controllers;

use App\Models\Analytic;
use App\Models\AnalyticUser;
use App\Models\User;
use App\Models\UserService;
use App\Models\UserWebinar;
use App\Models\VentureDeal;
use App\Models\Webinar;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StatsController extends Controller
{
    public function index() {
        $currentYear = Carbon::now()->year;
        $currentMonth = Carbon::now()->month;
        $accountarr = [];

        for ($month = 1; $month <= 12; $month++) {
            $startMonth = Carbon::create($currentYear, $month, 1)->startOfMonth();
            $endMonth = Carbon::create($currentYear, $month, 1)->endOfMonth();

            $count = User::whereBetween("created_at", [$startMonth, $endMonth])->count();
            $accountarr[] = $count;
        }
        $startMonth = Carbon::create($currentYear, $currentMonth, 1)->startOfMonth();
        $money = Webinar::where("created_at", ">=", Carbon::now()->subDays(30))->count();
        $money30d = Analytic::where("created_at", ">=", Carbon::now()->subDays(30))->count();
        $usersperday = UserService::where("created_at", ">=", Carbon::now()->subDays(30))->count();
        $logsperday = VentureDeal::where("created_at", ">=", Carbon::now()->subDays(30))->count();

        $analytics = Analytic::orderBy("downloads", "desc")->where("downloads", ">", "0")->get();
        $webinarsarr = Webinar::withCount("users")->orderBy("users_count", "desc")->get();
        $webinars = [];
        foreach ($webinarsarr as $web)
            if ($web->users_count != 0) $webinars[] = $web;

        return response()->json(["accounts" => $accountarr,
            "money" => $money, "money30" => $money30d, "usersPerDay" => $usersperday,
            "logsPerDay" => $logsperday, "analytics" => $analytics, "webinars" => $webinars],
            200);
    }
}
