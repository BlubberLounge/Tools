@extends('layouts.auth')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-11 col-md-8 col-xl-6">
            <div class="card">
                <div class="card-brand-logo">
                    <div class="d-flex justify-center py-3">
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
                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        <div class="row justify-content-center text-center text-secondary mb-3">
                            <p class="col-xl-8">
                                Fast geschafft! <br />
                                Nachdem sie das Formular ausgefüllt haben auf <span class="text-secondary-emphasis"> {{ __('register') }} </span> drücken.
                            </p>
                        </div>

                        <div class="row mb-3">
                            <label for="name" class="col-md-4 col-form-label text-md-end">{{ __('Name') }}</label>

                            <div class="col-md-6 position-relative">
                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror hasIcon" name="name" value="{{ old('name') }}" placeholder="Username" required autocomplete="name" autofocus>
                                <i class="fa-solid fa-user input-icon"></i>

                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-2">
                            <label for="firstname" class="col-md-4 col-form-label text-md-end">{{ __('Firstname') }}</label>

                            <div class="col-md-6 position-relative">
                                <input id="firstname" type="text" class="form-control @error('firstname') is-invalid @enderror hasIcon" name="firstname" value="{{ old('firstname') }}" placeholder="Max" required autocomplete="firstname">
                                <i class="fa-solid fa-user input-icon"></i>

                                @error('firstname')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-2">
                            <label for="lastname" class="col-md-4 col-form-label text-md-end">{{ __('Lastname') }}</label>

                            <div class="col-md-6 position-relative">
                                <input id="lastname" type="text" class="form-control @error('lastname') is-invalid @enderror hasIcon" name="lastname" value="{{ old('lastname') }}" placeholder="Mustermann" required autocomplete="lastname">
                                <i class="fa-solid fa-user input-icon"></i>

                                @error('lastname')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="email" class="col-md-4 col-form-label text-md-end">{{ __('Email Address') }}</label>

                            <div class="col-md-6 position-relative">
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror hasIcon" name="email" value="{{ old('email') }}" placeholder="muster.max@gmail.com" required autocomplete="email">
                                <i class="fa-solid fa-at"></i>

                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="dob" class="col-md-4 col-form-label text-md-end">{{ __('Date of Birth') }}</label>

                            <div class="col-md-6">
                                <input id="dob" type="date" class="form-control @error('dob') is-invalid @enderror" name="dob" value="{{ old('dob') }}" required autocomplete="off">

                                @error('dob')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="password" class="col-md-4 col-form-label text-md-end">{{ __('Password') }}</label>

                            <div class="col-md-6 position-relative">
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror hasIcon" name="password" required autocomplete="new-password">
                                <i class="fa-solid fa-key" id="password-toggler"></i>

                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="password-confirm" class="col-md-4 col-form-label text-md-end">{{ __('Confirm Password') }}</label>

                            <div class="col-md-6 position-relative">
                                <input id="password-confirm" type="password" class="form-control hasIcon" name="password_confirmation" required autocomplete="new-password">
                                <i class="fa-solid fa-key" id="password-toggler"></i>
                            </div>
                        </div>

                        <div class="row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('register') }}
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
