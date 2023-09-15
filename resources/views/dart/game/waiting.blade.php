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
    <div class="row">
        @foreach ($users as $user)
        <div class="col-12 col-md d-flex justify-center mb-3">
            <div class="card w-100 {{ $user->pivot->status === \App\Enums\DartGameUserStatus::ACCEPTED->value ? 'border-success' : ($user->pivot->status === \App\Enums\DartGameUserStatus::DENIED->value ? 'border-danger' : '') }} p-3" data-user-id="{{ $user->id }}">
                <div class="row align-items-center g-0 ">
                    <div class="col-3 me-3">
                        <img src="{{ $user->img }}" class="img rounded-circle" width="75px">
                    </div>
                    <div class="col-8">
                        <div class="card-body">
                            <h5 class="card-title">{{ $user->full_name }}</h5>
                            <p class="card-text"><small class="text-body-secondary user-status">{{ __($user->pivot->status) }}</small></p>
                        </div>
                    </div>
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
