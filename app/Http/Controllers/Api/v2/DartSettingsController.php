<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Api\v2\Controller;
use App\Models\DartUserSettings;
use Illuminate\Http\Request;

class DartSettingsController extends Controller
{
    public function show(Request $request)
    {
        $settings = DartUserSettings::where('user_id', $request->user()->id)->first();

        return response()->json([
            'settings' => $settings?->settings ?? (object) [],
        ]);
    }

    public function upsert(Request $request)
    {
        $validated = $request->validate([
            'settings' => ['required', 'array'],
        ]);

        $settings = DartUserSettings::updateOrCreate(
            ['user_id' => $request->user()->id],
            ['settings' => $validated['settings']],
        );

        return response()->json([
            'settings' => $settings->settings,
        ]);
    }
}
