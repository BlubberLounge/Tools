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
            Dart game
        </h3>
        <small class="text-muted">
            Darts, auch Dart (süddeutsch Spicken/Spicker/Spickern), ist ein Geschicklichkeitsspiel, eine Wurfsportart und ein Präzisionssport, bei dem mit Pfeilen (den Darts, süddeutsch Spickern) auf eine runde Scheibe (die Dartscheibe) geworfen wird.
        </small>
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
                3. Dartboard
            </button>
        </li>
      </ul>
      <div class="tab-content" id="myTabContent">
        <div class="tab-pane fade show active" id="home-tab-pane" role="tabpanel" aria-labelledby="home-tab" tabindex="0">
            <div class="row justify-center">
                <div class="col-md-7">
                    <div class="list-group">
                        @foreach ($users as $user)
                            <label class="list-group-item">
                                <input class="form-check-input me-1" type="checkbox" value="{{ $user->id }}">
                                {{ $user->full_name }}
                            </label>
                      @endforeach
                    </div>
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="profile-tab-pane" role="tabpanel" aria-labelledby="profile-tab" tabindex="0">
            Game Setting
        </div>
        <div class="tab-pane fade" id="contact-tab-pane" role="tabpanel" aria-labelledby="contact-tab" tabindex="0">
            <div class="row justify-center">
                <div class="col-md-6">
                    <div id="dartboard" style="width: 400px; height: 400px; position: relative;">
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
