@extends('layouts.app')

@push('scripts')
    <script src="{{ mix('js/dart.js') }}"></script>
@endpush

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/dartboard.css') }}" />
@endpush

@section('content')
<div class="container">

    <div class="row pb-4 pt-2">
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
                <button type="button" id="btnGameType-{{ $i }}01" class="btn btn-primary" data-bl-dart-game-type="{{ $i }}01">
                    <div class="p-2 p-md-4 d-flex flex-column text-center">
                        <i class="fa-solid fa-crosshairs mb-3" style="font-size: 2rem;"></i>
                        {{ $i }}01
                    </div>
                </button>
            </div>
        @endfor
    </div>

    <div class="row mb-4 g-1 g-md-3">
        <div class="col-auto">
            <button type="button" class="btn btn-primary">
                <div class="p-2 p-md-4 d-flex flex-column text-center">
                    <i class="fa-solid fa-arrows-rotate mb-3" style="font-size: 2rem;"></i>
                    Around the Clock
                </div>
            </button>
        </div>

        <div class="col-auto">
            <button type="button" class="btn btn-primary">
                <div class="p-2 p-md-4 d-flex flex-column text-center">
                    <i class="fa-solid fa-bullseye mb-3" style="font-size: 2rem;"></i>
                    Cricket
                </div>
            </button>
        </div>
        <div class="col-auto">
            <button type="button" class="btn btn-primary">
                <div class="p-2 p-md-4 d-flex flex-column text-center">
                    <i class="fa-solid fa-shield-heart mb-3" style="font-size: 2rem;"></i>
                    Cricket
                </div>
            </button>
        </div>
    </div>

    <ul class="nav nav-tabs" id="myTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#home-tab-pane" type="button" role="tab" aria-controls="home-tab-pane" aria-selected="true">
                1. Player
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile-tab-pane" type="button" role="tab" aria-controls="profile-tab-pane" aria-selected="false">
                2. Settings
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact-tab-pane" type="button" role="tab" aria-controls="contact-tab-pane" aria-selected="false">
                3. Play
            </button>
        </li>
      </ul>
      <div class="tab-content" id="myTabContent">
        <div class="tab-pane fade show active" id="home-tab-pane" role="tabpanel" aria-labelledby="home-tab" tabindex="0">
            <div class="row justify-center pt-4">
                <div class="col-md-7">
                    <div class="input-group mb-3">
                        <span class="input-group-text" id="basic-addon1"> Search </span>
                        <input type="search" id="SearchUser" class="form-control" placeholder="Username">
                    </div>
                    <ul class="list-group" id="ListUser">

                    </ul>
                    {{-- <ul class="list-group checkbox-list-group">
                        @foreach ($users as $user)
                            <li class="list-group-item">
                                <label class="form-check-label">&nbsp;
                                    <input class="form-check-input me-1 d-none" type="checkbox"  value="{{ $user->id }}">
                                    <span class="list-group-item-text">
                                        {{ $user->full_name }}
                                    </span>
                                </label>
                            </li>
                      @endforeach
                    </ul> --}}
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="profile-tab-pane" role="tabpanel" aria-labelledby="profile-tab" tabindex="0">
            Game Setting
        </div>
        <div class="tab-pane fade" id="contact-tab-pane" role="tabpanel" aria-labelledby="contact-tab" tabindex="0">
            <div class="row justify-center pt-4">
                <div class="col-md-6">
                    <div id="dartboard" style="width: 400px; height: 400px; position: relative;">
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
