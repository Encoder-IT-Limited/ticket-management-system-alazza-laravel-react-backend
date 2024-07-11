<?php

use App\Http\Resources\CoreSettingResource;
use App\Http\Resources\SettingResource;
use App\Models\CoreSetting;
use App\Models\Setting;
use App\Models\SocialAuthSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

function appSettings(): SettingResource
{
    $setting = Cache::rememberForever('settings', function () {
        return Setting::first();
    });

    return new SettingResource($setting);
}

function appCoreSettings()
{
    return CoreSetting::getAll();
//    return new CoreSettingResource(CoreSetting::getAll());
}

function socialAuthSettings()
{
    $settings = Cache::get('socialAuthSettings');
    if (!$settings) {
        $data = SocialAuthSetting::where('active', true)
            ->where('client_id', '!=', '')
            ->where('client_secret', '!=', '')
            ->where('redirect_url', '!=', '')
            ->get();
        Cache::put('socialAuthSettings', $data, 3600 * 60);
        $settings = $data;
    }
    return $settings;
}


function isTrashModeEnabled(): bool
{
    return auth()->check() && auth()->user()->trash_mode;
}
