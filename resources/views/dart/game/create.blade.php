@extends('layouts.dart')

@push('scripts')
    <script src="{{ mix('js/dartSetup.js') }}"></script>
@endpush

@section('content')
<div class="container-fluid px-4 p-md-0 mt-3">
    <div class="row me-0 justify-center">
        <form method="POST" id="dartGameCreateForm" action="{{ route('dart.game.store') }}" class="col col-md-8 col-lg-6">
            @csrf

            <section id="dart-game-header" class="mb-4">
                <div class="row">
                    <div class="col">
                        <h1>
                            {{ __('create dart lobby') }}
                        </h1>
                        <p> {{ __('Game type') }}: <u>{{ __($dartGameType) }}</u></p>
                    </div>
                    <div class="col-auto">
                        <div class="row mb-2">
                            <a href="{{ route('dart.game.index') }}" class="btn btn-outline-danger">
                                {{ __('cancel') }}
                            </a>
                        </div>
                        <div class="row mt-1">
                            <input class="btn btn-outline-success" type="submit" value="{{ __('start') }}">
                        </div>
                    </div>
                </div>
            </section>

            <section id="dart-game-settings" class="mb-4">
                <h5> {{ __('general') }} </h5>
                <input type="hidden" name="points" value="{{ $dartGameType }}">
                <x-form.input-text attribute="title" label="game title" bottomSpacing="3" />
                <x-form.input-checkbox attribute="private" label="private" bottomSpacing="3" />

                <div class="row mb-4">
                    <div class="col d-flex items-center">
                        {{ __('input') }}
                    </div>
                    <div class="col-auto">
                        <div class="btn-group" role="group" aria-label="Basic radio toggle button group">
                            <input type="radio" class="btn-check" name="inputType" id="inputType1" value="NUMPAD" autocomplete="off" disabled>
                            <label class="btn btn-outline-primary" for="inputType1"> {{ __('NumPad') }} </label>

                            <input type="radio" class="btn-check" name="inputType" id="inputType2" value="DARTBOARD" autocomplete="off" checked>
                            <label class="btn btn-outline-primary" for="inputType2">
                                {{ __('Dartboard') }}
                                <span class="badge rounded-pill text-bg-primary position-absolute translate-middle" style="font-size: .7em;left: 50%;top: 110%;">
                                    ({{ __('recommended') }})
                                </span>
                            </label>
                        </div>
                    </div>
                </div>
                @if(Str::endsWith($dartGameType, '01') && Str::length($dartGameType) == 3)
                    {{--
                    <h5> Checkin </h5>
                    <x-form.input-checkbox attribute="singleIn" label="Single In" helptext="Erster Dart darf ein Single-Feld sein" />
                    <x-form.input-checkbox attribute="doubleIn" label="Double In" helptext="Erster Dart darf ein Double-Feld sein" />
                    <x-form.input-checkbox attribute="trippleIn" label="Tripple In" helptext="Erster Dart darf ein Tripple-Feld sein" />
                    --}}
                    <h5> {{ __('checkout') }} </h5>
                    <x-form.input-checkbox attribute="singleOut" label="single out" helptext="last dart may be a single field" isChecked="true" />
                    <x-form.input-checkbox attribute="doubleOut" label="double out" helptext="last dart may be a double field" isChecked="true" />
                    <x-form.input-checkbox attribute="trippleOut" label="tripple out" helptext="last dart may be a tripple field" isChecked="true" />
                @elseif($dartGameType == 'cricket')
                    {{--  --}}
                    <p class="text-center"> Cricket - Keine Einstellungen Vorhanden </p>
                @else
                    <p class="text-center"> Keine Einstellungen Vorhanden </p>
                @endif
            </section>

            <hr class="mb-4" />

            <section id="dart-game-player">
                <h6> {{ __('selected player') }} </h6>
                <div class="mb-5" id="selectedUserList">
                    <div class="row d-flex align-items-center">
                        <input type="hidden" name="users[{{ Auth::user()->id  }}]" value="{{ Auth::user()->id  }}">
                        <div class="col-auto pe-1">
                            @if(Auth::user()->img)
                                <img src="{{ Auth::user()->img }}" width="32px" style="border-radius:50%">
                            @else
                                {!! Avatar::create(Auth::user()->name)->setDimension(32)->setFontSize(16)->toSvg() !!} {{-- https://github.com/laravolt/avatar --}}
                            @endif
                        </div>
                        <div class="col fs-5">
                            {{ __('me') }} {{-- (Auth::user()->full_name) --}}
                        </div>
                        <div class="col-auto actions">
                            <button type="button" class="btn btn-remove-player">
                                <i class="fa-solid fa-square-xmark fa-xl"></i>
                            </button>
                            <button type="button" class="btn btn-down-player">
                                <i class="fa-solid fa-circle-down fa-xl"></i>
                            </button>
                        </div>
                    </div>
                </div>


                <h5> {{ __('player selection') }} </h5>
                <div class="row justify-center">
                    <div class="col-md-7">
                        <ul class="list-group mb-3" id="ListUser"> </ul>
                        <div class="input-group">
                            <span class="input-group-text" id="basic-addon1"> {{ __('search') }} </span>
                            <input type="search" id="SearchUser" class="form-control" placeholder="{{ __('username') }}">
                        </div>
                    </div>
                </div>
            </section>
        </form>
    </div>
</div>
@endsection
