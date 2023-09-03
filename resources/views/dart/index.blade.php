@extends('layouts.app')

@push('scripts')
    <script src="{{ mix('js/dartIndex.js') }}"></script>
@endpush

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/dartboardResult.css') }}" />
@endpush

@section('content')
<div class="container">
    <div class="row mb-5 justify-center">
        <div class="col-8">
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
        <div class="col" style="height: 400px">
            <div id="dartboardContainer" class="d-flex position-relative justify-center align-items-center p-2 h-100 w-100">
                <div id="skeleton-dartboard" class="position-absolute top-50 start-50 translate-middle">
                    <div class="spinner-border">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col d-flex justify-center align-items-center h-100">
            <div id="graph01"></div>
        </div>
    </div>

    <div class="row justify-center align-items-center mb-5">
        <div id="expectationDataGraph" style="max-width: 100%"></div>
    </div>

    <div class="row justify-content-center px-4">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Set</th>
                    <th scope="col">Leg</th>
                    <th scope="col">Turn</th>
                    <th scope="col">Throw</th>
                    <th scope="col">Value</th>
                    <th scope="col">Field</th>
                    <th scope="col">Ring</th>
                    <th scope="col" class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    No Data...
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
