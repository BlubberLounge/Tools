<?php

namespace App\View\Components\ListItem;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

use App\Models\Device;
use App\Enums\DeviceType;


class ItemDevice extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        protected Device $device,
    ) {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $data['deviceTypeIcon'] = $this->getDeviceTypeIcon();
        $data['platformIcon'] = $this->getPlatformIcon();
        $data['familyIcon'] = $this->getBrowserIcon();
        $data['displayName'] = $this->getDisplayName();
        $data['lastActivity'] = $this->device->last_active->format('d.m.Y H:i:s');
        $data['isVerified'] = $this->device->isVerified;
        $data['loginCounter'] = $this->device->login_count;

        return view('components.list-item.item-device', $data);
    }

    private function getDeviceTypeIcon(): string
    {
        $iconClass = "fa-solid fa-bug";
        $type = $this->device->device_type;

        if(DeviceType::MOBILE === $type) {
            $iconClass = "fa-solid fa-mobile-screen";
        } else if(DeviceType::TABLET === $type) {
            $iconClass = "fa-solid fa-tablet-screen-button";
        } else if(DeviceType::DESKTOP === $type) {
            $iconClass = "fa-solid fa-desktop";
        } else if(DeviceType::BOT === $type) {
            $iconClass = "fa-solid fa-robot";
        } else {
            $iconClass = "fa-regular fa-circle-question";

            Log::channel('customMissing')->alert('User device type is missing in {class}: {device_type}', [
                'class' => get_class($this),
                'device_type' => $type,
            ]);
        }

        return $iconClass;
    }

    /**
     *
     */
    private function getPlatformIcon(): string
    {
        $iconClass = "fa-solid fa-bug";
        $platform = Str::lower($this->device->platform);

        if(Str::contains($platform, 'windows')) {
            $iconClass = "fa-brands fa-windows";
        } else if(Str::contains($platform, 'linux')) {
            $iconClass = "fa-brands fa-linux";
        } else if(Str::contains($platform, 'android')) {
            $iconClass = "fa-brands fa-android";
        } else if(Str::contains($platform, 'ios')) {
            $iconClass = "fa-brands fa-apple";
        } else {
            $iconClass = "fa-regular fa-circle-question";
            Log::channel('customMissing')->alert('User device Operating System is missing in {class}: {device_type}', [
                'class' => get_class($this),
                'device_type' => $platform,
            ]);
        }

        return $iconClass;
    }

    /**
     *
     */
    private function getBrowserIcon(): string
    {
        $iconClass = "fa-solid fa-bug";
        $browser = Str::lower($this->device->browser);

        if(Str::contains($browser, 'chrome')) { // includes Chrome mobile
            $iconClass = "fa-brands fa-chrome";
        } else if(Str::contains($browser, 'firefox')) {
            $iconClass = "fa-brands fa-firefox-browser";
        } else if(Str::contains($browser, 'safari')) {
            $iconClass = "fa-brands fa-safari";
        } else if(Str::contains($browser, 'samsung browser')) {
            $iconClass = "fa-brands fa-internet-explorer";
        } else {
            $iconClass = "fa-regular fa-circle-question";
            Log::channel('customMissing')->alert('User device browser is missing in {class}: {device_type}', [
                'class' => get_class($this),
                'device_type' => $browser,
            ]);
        }

        return $iconClass;
    }

    /**
     *
     */
    private function getDisplayName(): string
    {
        $browser = $this->device->browser_family;
        $platform = $this->device->platform_family;

        return "$platform ($browser)";

    }
}
