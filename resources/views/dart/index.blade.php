@extends('layouts.app')

@push('scripts')
    <script src="{{ mix('js/dartIndex.js') }}"></script>
@endpush

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/dartboardResult.css') }}" />
@endpush

@section('content')
<div class="container">
    <div class="row justify-content-around align-items-center mb-4">
        <div class="col-12 col-md rounded mb-4 mb-md-0 me-md-4" style="height: 600px;background-color: rgba(var(--bs-tertiary-bg-rgb), 1)">
            <div id="skillsGraph" style="height: 100%"></div>
        </div>
        <div class="col-12 col-md">
            <div id="winRateGraph" class="row rounded mb-4" style="height:280px;background-color: rgba(var(--bs-tertiary-bg-rgb), 1)"></div>
            <div id="positionGraph" class="row rounded" style="height:280px;background-color: rgba(var(--bs-tertiary-bg-rgb), 1)"></div>
        </div>
    </div>

    <div class="row justify-center align-items-center mb-4">
        <div id="activityGraph" class="col rounded" style="height:300px;background-color: rgba(var(--bs-tertiary-bg-rgb), 1)"></div>
    </div>

    <div class="row justify-content-around align-items-center mb-4">
        <div id="gameTypesGraph" class="col rounded me-4" style="height:280px;background-color: rgba(var(--bs-tertiary-bg-rgb), 1)"></div>
        <div id="" class="col rounded me-4" style="height:280px;background-color: rgba(var(--bs-tertiary-bg-rgb), 1)"></div>
        <div id="" class="col rounded" style="height:280px;background-color: rgba(var(--bs-tertiary-bg-rgb), 1)"></div>
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
