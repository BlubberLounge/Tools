@foreach ($users as $user)
@if($loop->odd)
    <div class="row pb-3 g-2"> {{--style="overflow-x: auto;white-space: nowrap;"--}}
@endif
    <div class="col-6">
        <div class="card d-inline-block w-100">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto" style="font-size: 2rem">
                        301
                    </div>
                    <div class="col " style="font-size: .85em">
                        <div class="row">
                            <div class="col">
                                {{ Str::limit($user->fullname, 11) }}
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                AVG: 300
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row g-1 text-center">
                    @for ($i = 1; $i <= 3; $i++ )
                        <div class="col">
                            {{ $i }}
                        </div>
                    @endfor
                    <div class="col">
                        Total
                    </div>
                </div>
                <div class="row g-1 text-center">
                    <div class="col">
                        180
                    </div>
                    <div class="col">
                        180
                    </div>
                    <div class="col">
                        180
                    </div>
                    <div class="col">
                        200
                    </div>
                </div>
            </div>
        </div>
    </div>
@if($loop->even || $loop->last)
    </div>
@endif
@endforeach
