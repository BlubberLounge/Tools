@extends('layouts.auth')

{{-- @push('scripts')
    @vite(['resources/js/auth.js'])
@endpush --}}

@section('content')
<div class="container mt-5 pt-4">
    <div class="flex flex-wrap justify-center">
        <div class="col-11 col-md">
            <div class="card flex-row border-0 shadow" style="--bs-border-radius:.75rem;">
                <div class="col hidden d-md-block rounded-start overlay-dark" style="background-image:url('/storage/img/blubberlounge_enamel_cup_03.jpg');background-size: cover;background-position: center;">
                    {{-- <h2 class="mt-5 relative" style="z-index: 2">Willkommen</h2> --}}
                </div>

                <div class="col col-md-5 p-md-5 pb-md-3">
                    <div class="card-brand-logo">
                        <div class="flex justify-center py-3">
                            <a class="flex items-center" href="{{ url('/') }}">
                                <img src="https://media.blubber-lounge.de/images/blubber_lounge_rebrand_try_white_optimized.svg" alt="BlubberLounge Logo" width="150px">
                            </a>
                            <div class="vertical-divider"></div>
                            <a class="nav-brand-sub flex items-center" href="{{ url('/') }}">
                                <i class="fa-solid fa-screwdriver-wrench" style="font-size: 2rem"></i>
                            </a>
                        </div>
                    </div>
                    {{-- <div class="card-brand-logo">
                        <div class="flex flex-col justify-center py-3">
                            <a class="nav-brand-sub flex flex-col items-center mb-4" href="{{ url('/') }}">
                                <i class="fa-solid fa-screwdriver-wrench" style="font-size: 2rem"></i>
                                <span> Tools </span>
                            </a>
                            <a class="flex items-center" href="{{ url('/') }}">
                                <img src="https://media.blubber-lounge.de/images/blubber_lounge_rebrand_try_white_optimized.svg" alt="BlubberLounge Logo" width="150px">
                            </a>
                        </div>
                    </div> --}}
                    <div class="card-body p-0 px-4 pb-4">
                        <form method="POST" action="{{ route('login') }}">
                            @csrf

                            <div class="row">
                                <label for="email" class="col-form-label">{{ __('Username or Email') }}</label>
                            </div>
                            <div class="flex flex-wrap mb-3">
                                <div class="relative">
                                    <input type="text"
                                        id="name"
                                        class="form-control @error('name') is-invalid @enderror hasIcon"
                                        name="name"
                                        value="{{ old('name') }}"
                                        placeholder="Type your Username or Email"
                                        required
                                        autofocus
                                    >
                                    <i class="fa-solid fa-user input-icon"></i>

                                    @error('email')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <label for="password" class="col-form-label">{{ __('Password') }}</label>
                            </div>
                            <div class="flex flex-wrap mb-3">
                                <div class="relative">
                                    <input id="password"
                                        type="password"
                                        class="form-control @error('password') is-invalid @enderror hasIcon"
                                        name="password"
                                        placeholder="Type your Password"
                                        required
                                        autocomplete="current-password"
                                    >
                                    <i class="fa-solid fa-key" id="password-toggler"></i>

                                    @error('password')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="flex flex-wrap mb-5">
                                <div class="col pr-0 flex items-center">
                                    <div class="form-check m-0">
                                        <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>

                                        <label class="form-check-label" for="remember">
                                            {{ __('Remember Me') }}
                                        </label>
                                    </div>
                                </div>
                                @if (Route::has('password.request'))
                                    <div class="col-auto p-0">
                                        <a class="btn btn-link" href="{{ route('password.request') }}">
                                            {{ __('Forgot Your Password?') }}
                                        </a>
                                    </div>
                                @endif
                            </div>

                            <div class="row">
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        {{ __('Login') }}
                                    </button>
                                </div>
                            </div>
                        </form>

                        @if(Route::has('blubberlounge.redirect'))
                            <div class="mt-2">
                                <div class="relative my-3 flex justify-center">
                                    <hr style="width: 75%;">
                                    <span class="absolute bg-card px-2 text-muted" style="top: 50%; transform: translateY(-50%); background-color: var(--bs-card-bg);">
                                        or
                                    </span>
                                </div>
                                <div class="flex justify-center items-center">
                                    <a href="{{ route('blubberlounge.redirect') }}" class="btn btn-bl-brand flex justify-center items-center">
                                        <img src="https://media.blubber-lounge.de/images/bubbles_rect.svg" width="20" class="mr-2">
                                        Sign in with BlubberLounge
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="flex justify-center w-100 absolute start-0" style="bottom: 1rem;">
        <span class="registerText"> Don't have an Account? </span>
        <a href="{{ route('register.request') }}" class="display-inline ml-2"> Request Access </a>
    </div>
</div>
@endsection
