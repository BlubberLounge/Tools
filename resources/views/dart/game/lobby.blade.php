@extends('layouts.dart')

@push('scripts')
    <script src="{{ mix('js/dartWaiting.js') }}"></script>
@endpush

@section('content')
<div class="container mt-3 px-4 px-md-0">
    <h1 class="text-center mb-2 mb-md-4">
        <input type="hidden" id="gameId" value="{{ $id }}">
        <i class="fa-solid fa-people-pulling fa-flip-horizontal"></i>
        {{ __(' waiting for players ') }}
        <i class="fa-solid fa-people-pulling"></i>
    </h1>
    <div class="row text-center mb-2 mb-md-4">
        <p class="text-body-secondary">
            {{ __('Before the game can begin, each player from the list must accept the game. Each player has received an email notification. Players who have accepted the game have a green frame. Players who have declined the game have a red frame. After all players have accepted the game will start automatically.') }}
        </p>
    </div>
    <div class="row mb-5">
        @foreach ($users as $user)
            <div class="col-6 col-md d-flex justify-center flex-column">
                <div class="row justify-center mb-2">
                    <div class="col-auto">
                        <img src="{{ $user->img }}" width="96" class="rounded-circle border border-3 {{ $user->pivot->status === \App\Enums\DartGameUserStatus::ACCEPTED->value ? 'border-success' : ($user->pivot->status === \App\Enums\DartGameUserStatus::DENIED->value ? 'border-danger' : '') }} p-1" data-user-id="{{ $user->id }}">
                    </div>
                </div>
                <div class="row text-center">
                    <div class="col">
                        <h5>{{ $user->full_name }}</h5>
                        <p class="card-text"><small class="text-body-secondary user-status">{{ __($user->pivot->status) }}</small></p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row justify-center mt-3">
        <div class="col-auto btn-group" role="group" aria-label="Basic example">
            <button id="btnRefresh" class="btn btn-primary">{{ __('refresh') }}</button>
            <a href="{{ route('home') }}" class="btn btn-warning">{{ __('abort') }}</a>
        </div>
    </div>
</div>
@endsection
