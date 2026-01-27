@extends('layouts.app')

@push('styles')
    @vite(['resources/sass/dartboard.scss'])
@endpush

@section('content')
<div class="container mx-auto px-4">

    <div class="mb-8 pt-2 text-center">
        <i class="fa-solid fa-computer fa-4x"></i>
        <h1 class="text-2xl font-bold mt-2"> {{ __('dart game') }} </h1>
        <small class="text-muted">
            {{ __('darts or dart-throwing is a competitive sport in which two or more players bare-handedly throw small sharp-pointed missiles known as darts at a round target known as a dartboard.') }}
        </small>
    </div>

    <div class="flex flex-wrap gap-1 md:gap-3 justify-center mb-4">
        @for($i = 2; $i <= 8; $i++)
            <div>
                <a href="{{ route('dart.game.create', ['type' => $i.'01']) }}" id="btnGameType-{{ $i }}01" class="btn btn-primary" data-bl-dart-game-type="{{ $i }}01" role="button" x-tooltip="'{{ __('popover.dartX01', ['points' => $i.'01']) }}'">
                    <div class="p-2 md:p-4 flex flex-col text-center">
                        <i class="fa-solid fa-crosshairs mb-3 text-3xl"></i>
                        {{ $i }}01
                    </div>
                </a>
            </div>
        @endfor
    </div>

    <div class="flex flex-wrap gap-1 md:gap-3 justify-center mb-8">
        <div>
            <a href="{{ route('dart.game.create', ['type' => 'aroundTheClock']) }}" class="btn btn-primary disabled" role="button">
                <div class="p-2 md:p-4 flex flex-col text-center">
                    <i class="fa-solid fa-repeat mb-3 text-3xl"></i>
                    {{ __('around the clock') }}
                </div>
            </a>
        </div>

        <div>
            <a href="{{ route('dart.game.create', ['type' => 'cricket']) }}" class="btn btn-primary disabled" role="button">
                <div class="p-2 md:p-4 flex flex-col text-center">
                    <i class="fa-solid fa-bullseye mb-3 text-3xl"></i>
                    {{ __('cricket') }}
                </div>
            </a>
        </div>
        <div>
            <a href="{{ route('dart.game.create', ['type' => 'cricket']) }}" class="btn btn-primary disabled" role="button">
                <div class="p-2 md:p-4 flex flex-col text-center">
                    <i class="fa-solid fa-graduation-cap mb-3 text-3xl"></i>
                    {{ __('practise') }}
                </div>
            </a>
        </div>
    </div>
</div>
@endsection
