@extends('layouts.auth')

@section('content')
<div class="container mt-3 mt-md-5 pt-md-4">
    <div class="row justify-content-center">
        <div class="col-11 col-md-7 col-xl-5">
            <div class="card">
                <div class="card-brand-logo">
                    <div class="d-flex justify-center my-2">
                        <a class="d-flex align-items-center" href="{{ url('/') }}">
                            <img src="https://media.maximilian-mewes.de/project/bl/blubber_lounge_rebrand_try_white_optimized.svg" alt="BlubberLounge Logo" width="150px">
                        </a>
                        <div class="vertical-divider"></div>
                        <a class="nav-brand-sub d-flex align-items-center" href="{{ url('/') }}">
                            <i class="fa-solid fa-screwdriver-wrench" style="font-size: 2rem"></i>
                        </a>
                    </div>
                </div>
               <div class="card-body p-0 px-4 pb-4">
                    <form method="POST" action="{{ route('register.request.store') }}">
                        @csrf
                        @method('PUT')

                        <div class="row text-secondary">
                            <p>
                                Um den Zugang zu dieser Applikation zu beantragen trage deinen Vornamen, Nachnamen und E-mail in die Felder ein. Um den Antrag zu verschicken auf <span class="text-secondary-emphasis">{{ __('Send Access Request') }}</span> drücken. <br>
                                Die durchschnittliche Bearbeitungszeit beträgt: <span class="text-secondary-emphasis">{{ now()->diffForHumans(now()->addWeeks(4), true) }}</span>.
                            </p>
                        </div>

                        <div class="row">
                            <label for="firstname" class="col-form-label">{{ __('firstname') }}</label>
                        </div>
                        <div class="row mb-3">
                            <div class="position-relative">
                                <input type="text"
                                    id="firstname"
                                    class="form-control @error('firstname') is-invalid @enderror"
                                    name="firstname"
                                    value="{{ old('firstname') }}"
                                    placeholder="Firstname"
                                    required
                                >

                                @error('firstname')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <label for="lastname" class="col-form-label">{{ __('lastname') }}</label>
                        </div>
                        <div class="row mb-3">
                            <div class="position-relative">
                                <input type="text"
                                    id="lastname"
                                    class="form-control @error('lastname') is-invalid @enderror"
                                    name="lastname"
                                    value="{{ old('lastname') }}"
                                    placeholder="Lastname"
                                    required
                                >

                                @error('lastname')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <label for="email" class="col-form-label">{{ __('email') }}</label>
                        </div>
                        <div class="row mb-3">
                            <div class="position-relative">
                                <input type="email"
                                    id="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                    name="email"
                                    value="{{ old('email') }}"
                                    placeholder="Type your Email"
                                    required
                                    autofocus
                                >

                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Send Access Request') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
