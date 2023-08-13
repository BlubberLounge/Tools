@extends('layouts.app')

{{-- @push('scripts')
    <script src="{{ mix('js/dart.js') }}"></script>
@endpush --}}

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

    <div class="row mb-4 g-1 g-md-3 justify-center">
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

    <div class="row mb-5 g-1 g-md-3 justify-center">
        <div class="col-auto">
            <a href="{{ route('dart.create', ['type' => 'aroundTheClock']) }}" class="btn btn-primary disabled" role="button">
                <div class="p-2 p-md-4 d-flex flex-column text-center">
                    <i class="fa-solid fa-repeat mb-3" style="font-size: 2rem;"></i>
                    Around the Clock
                </div>
            </a>
        </div>

        <div class="col-auto">
            <a href="{{ route('dart.create', ['type' => 'cricket']) }}" class="btn btn-primary disabled" role="button">
                <div class="p-2 p-md-4 d-flex flex-column text-center">
                    <i class="fa-solid fa-bullseye mb-3" style="font-size: 2rem;"></i>
                    Cricket
                </div>
            </a>
        </div>
        <div class="col-auto">
            <a href="{{ route('dart.create', ['type' => 'cricket']) }}" class="btn btn-primary disabled" role="button">
                <div class="p-2 p-md-4 d-flex flex-column text-center">
                    <i class="fa-solid fa-graduation-cap mb-3" style="font-size: 2rem;"></i>
                    {{-- <i class="fa-solid fa-golf-ball-tee mb-3" style="font-size: 2rem;"></i> --}}
                    Practise
                </div>
            </a>
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
