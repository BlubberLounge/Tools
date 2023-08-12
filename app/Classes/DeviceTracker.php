<?php

namespace App\Classes;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Browser;

use App\Models\Device;

class DeviceTracker
{

    /**
     *
     */
    public static function detect(bool $updateLastActive = false)
    {
        // $result = Browser::parse('Mozilla/5.0 (IPhone; CPU IPhone OS 16_5 like Max OS X) AppleWebKit/605.1.15 (KHTML,like Gecko) Version/16.5 Mobile/15E148 Safari/604.1');
        $result = Browser::detect();

        $device_type = Str::lower($result->deviceType());
        $device_family = $result->deviceFamily();
        $device_model = $result->deviceModel();
        $browser = $result->browserName();
        $platform = $result->platformName();

        $device = Device::where('user_id', Auth::user()->id)
            ->where('device_type', $device_type)
            ->where('device_family',  $device_family)
            ->where('device_model', $device_model)
            ->where('browser', $browser)
            ->where('platform', $platform)
            ->where('ip', Request::ip())
            ->first();

        if(is_null($device)) {
            $device = new Device;
            $device->user_id = Auth::user()->id;
            $device->device_type = $device_type;
            $device->device_family = $device_family;
            $device->device_model = $device_model;
            $device->device_grade = $result->mobileGrade();

            $device->browser = $browser;
            $device->browser_family = $result->browserFamily();
            $device->browser_version = $result->browserVersion();

            $device->platform = $platform;
            $device->platform_family = $result->platformFamily();
            $device->platform_version = $result->platformVersion();

            $device->data = [
                'ip_addresses' => Request::ips(),
                'user_agent' => Str::limit(Request::header('user-agent'), 512),
            ];
            $device->ip = Request::ip();
        }

        if($updateLastActive)
            $device->last_active = now();

        if($device->isDirty())
            $device->save();

        return $device;
    }

    /**
     *
     */
    public static function detectLogin()
    {
        if(!Auth::guard('web')->check())
            return;

        $d = self::detect(false);
        $d->login_count += 1;
        $d->save();
    }

    /**
     *
     */
    public static function detectRegistration()
    {
        if(!Auth::guard('web')->check())
            return;

        self::detectLogin();
    }
};
