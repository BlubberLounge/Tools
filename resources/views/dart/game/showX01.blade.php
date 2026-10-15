@foreach ($users as $user)
@if($loop->odd)
    <div class="flex flex-wrap pb-3 gap-2"> {{--style="overflow-x: auto;white-space: nowrap;"--}}
@endif
    <div class="w-6/12">
        <div @class(["playercard inline-block w-full", "bg-neutral text-neutral-content" => $loop->first]) data-user-id="{{ $user->id }}">
            <div class="playercard-body">
                <div class="flex flex-wrap m-0 items-center">
                    <div class="w-auto playercard-total-points total">
                        {{ $points }}
                    </div>
                    <div class="flex-1 pr-0" style="font-size: .85em">
                        <div class="flex flex-wrap">
                            <div class="flex-1">
                                {{ Str::limit($user->fullname, 11) }}
                            </div>
                        </div>
                        <div class="flex flex-wrap">
                            <div class="flex-1">
                                AVG: <span class="avg">0</span>
                            </div>
                            <div class="flex-1">
                                <img src="{{ $user->img }}" width="25px" style="border-radius:50%">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex flex-wrap gap-1 text-center">
                    @for ($i = 1; $i <= 3; $i++ )
                        <div class="flex-1 playercard-throw-count">
                            {{ $i }}
                        </div>
                    @endfor
                    <div class="flex-1"  style=" padding: .275rem 0 0 0;">
                        Total
                    </div>
                </div>
                <div class="flex flex-wrap gap-1 text-center">
                    @for ($i = 1; $i <= 3; $i++ )
                        <div class="flex-1 font-bold playercard-throw-points">
                            <span class="throw-{{ $i }}">
                                <i class="fa-solid fa-xmark text-error"></i>
                            </span>
                        </div>
                    @endfor
                    <div class="flex-1 font-bold turn-total">
                        0
                    </div>
                </div>
            </div>
        </div>
    </div>
@if($loop->even || $loop->last)
    </div>
@endif
@endforeach
