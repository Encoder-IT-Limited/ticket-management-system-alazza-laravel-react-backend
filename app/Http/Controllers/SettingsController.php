<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function updateLogo(Request $request)
    {
        $request->validate([
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        $logo = $request->file('logo');
        $logoPath = $logo->store('logos', 'public');

       Setting::updateOrCreate(
            ['key' => 'logo'],
            ['value' => $logoPath]
        );

        return response()->json([
            'logo_path' => $logoPath
        ]);
    }

    public function getCompanyLogo(){
        $setting = Setting::where('key', 'logo')->first();

        return response()->json([
            'logo' => $setting
        ]);
    }
}
