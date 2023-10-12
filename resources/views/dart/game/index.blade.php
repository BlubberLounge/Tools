@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/dartboard.css') }}" />
@endpush

@section('content')
<div class="container">

    <div class="row mb-5 pt-2 text-center">
        <i class="fa-solid fa-computer fa-4x"></i>
        <h1> {{ __('dart game') }} </h1>
        <small class="text-muted">
            {{ __('darts or dart-throwing is a competitive sport in which two or more players bare-handedly throw small sharp-pointed missiles known as darts at a round target known as a dartboard.') }}
        </small>
    </div>

    <div class="row mb-4 g-1 g-md-3 justify-center">
        @for($i = 2; $i <= 8; $i++)
            <div class="col-auto" >
                <a href="{{ route('dart.game.create', ['type' => $i.'01']) }}" id="btnGameType-{{ $i }}01" class="btn btn-primary" data-bl-dart-game-type="{{ $i }}01" role="button">
                    <div class="p-2 p-md-4 d-flex flex-column text-center" data-bs-toggle="tooltip" data-bs-title="{{ __('popover.dartX01', ['points' => $i.'01']) }}">
                        <i class="fa-solid fa-crosshairs mb-3" style="font-size: 2rem;"></i>
                        {{ $i }}01
                    </div>
                </a>
            </div>
        @endfor
    </div>

    <div class="row mb-5 g-1 g-md-3 justify-center">
        <div class="col-auto">
            <a href="{{ route('dart.game.create', ['type' => 'aroundTheClock']) }}" class="btn btn-primary disabled" role="button">
                <div class="p-2 p-md-4 d-flex flex-column text-center">
                    <i class="fa-solid fa-repeat mb-3" style="font-size: 2rem;"></i>
                    {{ __('around the clock') }}
                </div>
            </a>
        </div>

        <div class="col-auto">
            <a href="{{ route('dart.game.create', ['type' => 'cricket']) }}" class="btn btn-primary disabled" role="button">
                <div class="p-2 p-md-4 d-flex flex-column text-center">
                    <i class="fa-solid fa-bullseye mb-3" style="font-size: 2rem;"></i>
                    {{ __('cricket') }}
                </div>
            </a>
        </div>
        <div class="col-auto">
            <a href="{{ route('dart.game.create', ['type' => 'cricket']) }}" class="btn btn-primary disabled" role="button">
                <div class="p-2 p-md-4 d-flex flex-column text-center">
                    <i class="fa-solid fa-graduation-cap mb-3" style="font-size: 2rem;"></i>
                    {{ __('practise') }}
                </div>
            </a>
        </div>
    </div>
</div>
@endsection
