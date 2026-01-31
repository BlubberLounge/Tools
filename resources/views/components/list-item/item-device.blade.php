<div class="flex flex-wrap mb-3">
    <div class="col-2 flex justify-center">
        <i class="{{ $deviceTypeIcon }} device-browser-icon"></i>
    </div>
    <div class="col ml-1">
        <div class="row">
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
        <div class="row">
            <div class="col">
                <span class="text-muted">
                    Letzte aktivität: {{ $lastActivity }}
                </span>
            </div>
            <div class="col-auto">
                <span class="text-muted">
                    {{ $loginCounter }}
                </span>
            </div>
        </div>
    </div>
</div>
<hr />
