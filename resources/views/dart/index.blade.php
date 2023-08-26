@extends('layouts.app')

@push('scripts')
    <script src="{{ mix('js/dartIndex.js') }}"></script>
@endpush

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/dartboardResult.css') }}" />
@endpush

@section('content')
<div class="container">
    <div class="row mb-5">
        <div class="col" style="height: 400px">
            <input type="hidden" id="gameId" name="id" value="99ec098a-cd74-4223-ab1f-3bca516ce8fa">
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

    <div class="row mb-4">
        <div class="col">
            <ul class="list-group">
                @foreach ($games as $game)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        @mobile
                            {{ Str::limit($game->title, 40) }}
                        @endmobile
                        @desktop
                            {{ $game->title }}
                        @enddesktop
                        <span class="badge rounded-pill" style="background-color: {{ $game->status->color() }}"> {{ $game->status }} </span>
                    </li>
                @endforeach
              </ul>
        </div>
    </div>
</div>
@endsection
