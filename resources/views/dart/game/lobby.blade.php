@extends('layouts.dart')

@push('scripts')
    @vite(['resources/js/dart/game/dartWaiting.js'])
@endpush

@section('content')
<div class="container mt-3 px-4 md:px-0">
    <h1 class="text-center mb-2 md:mb-4">
        <input type="hidden" id="gameId" value="{{ $id }}">
        <i class="fa-solid fa-people-pulling fa-flip-horizontal"></i>
        {{ __(' waiting for players ') }}
        <i class="fa-solid fa-people-pulling"></i>
    </h1>
    <div class="flex flex-wrap text-center mb-2 md:mb-4">
        <p class="text-base-content/50">
            {{ __('Before the game can begin, each player from the list must accept the game. Each player has received an email notification. Players who have accepted the game have a green frame. Players who have declined the game have a red frame. After all players have accepted the game will start automatically.') }}
        </p>
    </div>
    <div class="flex flex-wrap mb-5">
        @foreach ($users as $user)
            <div class="w-6/12 md:flex-1 flex justify-center flex-col" data-user-id="{{ $user->id }}">
                <div class="flex flex-wrap justify-center mb-2">
                    <div class="w-auto">
                        <img src="{{ $user->img }}" width="96" class="rounded-full border-[3px] {{ $user->pivot->status === \App\Enums\DartGameUserStatus::ACCEPTED->value ? 'border-success' : ($user->pivot->status === \App\Enums\DartGameUserStatus::DENIED->value ? 'border-error' : '') }} p-1">
                    </div>
                </div>
                <div class="flex flex-wrap text-center">
                    <div class="flex-1">
                        <h5>{{ $user->full_name }}</h5>
                        <p class="card-text"><small class="text-base-content/50 user-status">{{ __($user->pivot->status) }}</small></p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="flex flex-wrap justify-center mt-3">
        <div class="w-auto join" role="group" aria-label="Basic example">
            <button id="btnRefresh" class="btn btn-primary join-item">{{ __('refresh') }}</button>
            <a href="{{ route('home') }}" class="btn btn-warning join-item">{{ __('abort') }}</a>
        </div>
    </div>
</div>
@endsection
