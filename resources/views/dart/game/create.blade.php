@extends('layouts.dart')

@push('scripts')
    <script src="{{ mix('js/dartSetup.js') }}"></script>
@endpush

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/dart.css') }}" />
@endpush

@section('content')
<div class="container px-4">
    <form method="POST" action="{{ route('dart.store') }}">
        @csrf

        <section id="dart-game-header" class="mb-4">
            <div class="row">
                <div class="col">
                    <h1>
                        <i class="fa-solid fa-sliders"></i> Dart Settings
                    </h1>
                    <p> Game type: <u>{{ $dartGameType }}</u></p>
                </div>
                <div class="col-auto">
                    <div class="row mb-2">
                        <a href="{{ route('dart.index') }}" class="btn btn-outline-danger">
                            Abbrechen
                        </a>
                    </div>
                    <div class="row mt-1">
                        <input class="btn btn-outline-success" type="submit" value="Start">
                    </div>
                </div>
            </div>
        </section>

        <section id="dart-game-settings" class="mb-4">
            @if(Str::endsWith($dartGameType, '01') && Str::length($dartGameType) == 3)
                <h5> General </h5>
                <x-form.input-text attribute="gameTitle" label="Game title" />
                <x-form.input-checkbox attribute="private" label="Private" />
                {{--
                <h5> Checkin </h5>
                <x-form.input-checkbox attribute="singleIn" label="Single In" helptext="Erster Dart darf ein Single-Feld sein" />
                <x-form.input-checkbox attribute="doubleIn" label="Double In" helptext="Erster Dart darf ein Double-Feld sein" />
                <x-form.input-checkbox attribute="trippleIn" label="Tripple In" helptext="Erster Dart darf ein Tripple-Feld sein" />
                --}}
                <h5 class="mt-4"> Checkout </h5>
                <x-form.input-checkbox attribute="singleOut" label="Single Out" helptext="Letzter Dart darf ein Single-Feld sein" isChecked="true" />
                <x-form.input-checkbox attribute="doubleOut" label="Double Out" helptext="Letzter Dart darf ein Double-Feld sein" isChecked="true" />
                <x-form.input-checkbox attribute="trippleOut" label="Tripple Out" helptext="Letzter Dart darf ein Tripple-Feld sein" isChecked="true" />
            @elseif($dartGameType == 'cricket')
                {{--  --}}
                <p class="text-center"> Cricket </p>
            @else
                <p class="text-center"> Keine Einstellungen Vorhanden </p>
            @endif
        </section>

        <hr class="mb-4" />

        <section id="dart-game-player">
            <h6> Selected Player </h6>
            <div class="mb-5" id="selectedUserList">
                <div class="row">
                    <div class="col fs-5">
                        Ich ({{ Auth::user()->full_name }})
                    </div>
                    <div class="col-auto">
                        {{-- <button class="btn btn-add-player" style="color:var(--bs-danger)">
                            <i class="fa-solid fa-square-xmark fa-xl"></i>
                        </button> --}}
                        {{-- <i class="fa-solid fa-circle-down fa-xl"></i> --}}
                    </div>
                </div>
            </div>


            <h5> Player selection </h5>
            <div class="row justify-center">
                <div class="col-md-7">
                    <ul class="list-group mb-3" id="ListUser"> </ul>
                    <div class="input-group">
                        <span class="input-group-text" id="basic-addon1"> Search </span>
                        <input type="search" id="SearchUser" class="form-control" placeholder="Username">
                    </div>
                </div>
            </div>
        </section>
    </form>
</div>
@endsection
