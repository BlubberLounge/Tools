<div class="flex flex-wrap mb-3">
    <div class="w-1/6 flex justify-center">
        <i class="{{ $deviceTypeIcon }} device-browser-icon"></i>
    </div>
    <div class="flex-1 ml-1">
        <div class="flex flex-wrap">
            <span>
                <i class="{{ $familyIcon }}" style="font-size: .9rem"></i>
                <i class="{{ $platformIcon }}" style="font-size: .9rem"></i>
                {{ $displayName }}
                @if ($isVerified)
                    <span class="ml-2" style="font-size: .8rem;color: var(--bl-clr-green);">
                        <i class="fa-regular fa-circle-check"></i>
                        verified
                    <span>
                @endif
            </span>
        </div>
        <div class="flex flex-wrap">
            <div class="flex-1">
                <span class="text-base-content/50">
                    Letzte aktivität: {{ $lastActivity }}
                </span>
            </div>
            <div class="shrink-0">
                <span class="text-base-content/50">
                    {{ $loginCounter }}
                </span>
            </div>
        </div>
    </div>
</div>
<hr />
