<?php

namespace App\Http\Controllers;

use App\Models\Analytic;
use App\Models\Service;
use App\Models\UserService;
use App\Models\Webinar;
use Carbon\Carbon;
use Illuminate\Http\Request;

class FeedController extends Controller
{
    public function all (Request $request) {
        $response = [];

        $response["upcoming_events"] = Webinar::where("date", ">", Carbon::now())
            ->orderBy("date", "asc")->get();

        $response["old_events"] = Webinar::where("date", "<", Carbon::now())
            ->orderBy("date", "desc")->limit(20)->get();

        $response["analytics"] = Analytic::limit(15)->get();
        $response["services"] = Service::all();
        foreach ($response["analytics"] as $event) {
            $event->time();

            if ($event->locked) {
                unset($event->pdf);
                $event->description = substr($event->description, 0, 100) . "...";
            }
        }

        return response()->json($response);
    }

    public function analytics (Request $request) {
        $response = Analytic::offset($request->offset)->limit(15)->get();
        foreach ($response as $event) {
            $event->time();

            if ($event->locked) {
                unset($event->pdf);
                $event->description = substr($event->description, 0, 100) . "...";
            }
        }

        return response()->json($response);
    }

    public function webinars (Request $request) {
        $response = Webinar::where("date", "<", Carbon::now())
            ->orderBy("date", "desc")->offset($request->offset)->limit(20)->get();

        return response()->json($response);
    }
}
