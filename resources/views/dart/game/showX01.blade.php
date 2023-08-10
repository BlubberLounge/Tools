@foreach ($users as $user)
@if($loop->odd)
    <div class="row pb-3 g-2"> {{--style="overflow-x: auto;white-space: nowrap;"--}}
@endif
    <div class="col-6">
        <div @class(["playercard d-inline-block w-100", "text-bg-secondary" => $loop->first]) data-user-id="{{ $user->id }}">
            <div class="playercard-body">
                <div class="row m-0 align-items-center">
                    <div class="col-auto playercard-total-points total">
                        {{ $points }}
                    </div>
                    <div class="col " style="font-size: .85em">
                        <div class="row">
                            <div class="col">
                                {{ Str::limit($user->fullname, 11) }}
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                AVG: <span class="avg">0</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row g-1 text-center">
                    @for ($i = 1; $i <= 3; $i++ )
                        <div class="col playercard-throw-count">
                            {{ $i }}
                        </div>
                    @endfor
                    <div class="col"  style=" padding: .275rem 0 0 0;">
                        Total
                    </div>
                </div>
                <div class="row g-1 text-center">
                    @for ($i = 1; $i <= 3; $i++ )
                        <div class="col fw-bold playercard-throw-points">
                            <span class="throw-{{ $i }}">
                                <i class="fa-solid fa-xmark text-danger"></i>
                            </span>
                        </div>
                    @endfor
                    <div class="col fw-bold turn-total">
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
