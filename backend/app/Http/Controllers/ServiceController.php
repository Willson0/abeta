<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function show ($id, Request $request) {

    }

    public function index (Request $request) {
        return response()->json(Service::all());
    }
}
