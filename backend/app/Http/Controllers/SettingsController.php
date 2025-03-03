<?php

namespace App\Http\Controllers;

use App\Http\Requests\SettingsRequest;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function settings (SettingsRequest $request) {
        $data = $request->validated();
        foreach ($data as $key=>$value)
            utils::updateSettings($key, $value);

        return response()->json(utils::getSettings());
    }
}
