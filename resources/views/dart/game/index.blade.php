@extends('layouts.app')

@push('scripts')
    <script src="{{ mix('js/dart.js') }}"></script>
@endpush

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/dartboard.css') }}" />
@endpush

@section('content')
<div class="container">

    <div class="row mb-5 pt-2">
        <h3 class="col-8">
            Dart Spielmodi
        </h3>
        <small class="text-muted">
            Darts, auch Dart (süddeutsch Spicken/Spicker/Spickern), ist ein Geschicklichkeitsspiel, eine Wurfsportart und ein Präzisionssport, bei dem mit Pfeilen (den Darts, süddeutsch Spickern) auf eine runde Scheibe (die Dartscheibe) geworfen wird.
        </small>
    </div>

    <div class="row mb-4 g-1 g-md-3">
        @for($i = 3; $i <= 7; $i++)
            <div class="col-auto">
                <a href="{{ route('dart.create', ['type' => $i.'01']) }}" id="btnGameType-{{ $i }}01" class="btn btn-primary" data-bl-dart-game-type="{{ $i }}01" role="button">
                    <div class="p-2 p-md-4 d-flex flex-column text-center">
                        <i class="fa-solid fa-crosshairs mb-3" style="font-size: 2rem;"></i>
                        {{ $i }}01
                    </div>
                </a>
            </div>
        @endfor
    </div>

    <div class="row mb-4 g-1 g-md-3">
        <div class="col-auto">
            <a href="{{ route('dart.create', ['type' => 'aroundTheClock']) }}" class="btn btn-primary" role="button">
                <div class="p-2 p-md-4 d-flex flex-column text-center">
                    <i class="fa-solid fa-arrows-rotate mb-3" style="font-size: 2rem;"></i>
                    Around the Clock
                </div>
            </a>
        </div>

        <div class="col-auto">
            <a href="{{ route('dart.create', ['type' => 'cricket']) }}" class="btn btn-primary" role="button">
                <div class="p-2 p-md-4 d-flex flex-column text-center">
                    <i class="fa-solid fa-bullseye mb-3" style="font-size: 2rem;"></i>
                    Cricket
                </div>
            </a>
        </div>
        <div class="col-auto">
            <a href="{{ route('dart.create', ['type' => 'cricket']) }}" class="btn btn-primary" role="button">
                <div class="p-2 p-md-4 d-flex flex-column text-center">
                    <i class="fa-solid fa-shield-heart mb-3" style="font-size: 2rem;"></i>
                    Cricket
                </div>
            </a>
        </div>
    </div>
</div>
@endsection
