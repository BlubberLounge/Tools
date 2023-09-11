@extends('layouts.app')

@push('scripts')
    <script src="{{ mix('js/dartIndex.js') }}"></script>
@endpush

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/dartboardResult.css') }}" />
@endpush

@section('content')
<div class="container">
    <div class="row justify-center align-items-center mb-5">
        <div id="activityGraph" class="col" style="height:300px;"></div>
    </div>
    <div class="row justify-center align-items-center mb-5">
        <div id="winRateGraph" class="col-12 col-md" style="height:300px;"></div>
        <div id="positionGraph" class="col-12 col-md" style="height:300px;"></div>
    </div>

    <div class="row mb-5 justify-center">
        <div class="col-10 col-md-7">
            <select id="gameSelection" class="form-select">
                {{-- <option selected>Open this select menu</option> --}}
                @foreach ($games as $game)
                    <option value="{{ $game->id }}" {{ $loop->first ? 'selected' : null }}>
                        @mobile
                            {{ Str::limit($game->title, 25) }}
                        @endmobile
                        @desktop
                            {{ $game->title }}
                        @enddesktop
                        {{-- <span class="badge rounded-pill" style="background-color: {{ $game->status->color() }}"> {{ $game->status }} </span> --}}
                    </option>
                @endforeach

            </select>
        </div>
    </div>
    <div class="row mb-5">
        <div class="col col-md-6" style="height: 400px">
            <div id="dartboardContainer" class="d-flex position-relative justify-center align-items-center p-2 h-100 w-100">
                <div id="skeleton-dartboard" class="position-absolute top-50 start-50 translate-middle">
                    <div class="spinner-border">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col col-md-6 d-flex justify-center align-items-center h-100">
            <div id="graph01"></div>
        </div>
    </div>

    <div class="row justify-center align-items-center mb-5">
        <div id="expectationDataGraph" class="col" style="height: 500px;"></div>
    </div>
</div>
@endsection
