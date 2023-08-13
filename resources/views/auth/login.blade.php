@extends('layouts.auth')

{{-- @push('scripts')
    <script src="{{ mix('js/auth.js') }}" defer></script>
@endpush --}}

@section('content')
<div class="container mt-5 pt-4">
    <div class="row justify-content-center">
        <div class="col-11 col-md-7 col-xl-5">
            <div class="card">
                <div class="card-brand-logo">
                    <img src="https://media.maximilian-mewes.de/project/bl/blubber_lounge_rebrand_try_white_optimized.svg" alt="BlubberLounge logo" width="150px">
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="row">
                            <label for="email" class="col-form-label">{{ __('Username or Email') }}</label>
                        </div>
                        <div class="row mb-3">
                            <div class="position-relative">
                                <input type="text"
                                    id="name"
                                    class="form-control @error('name') is-invalid @enderror"
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
                        <div class="row mb-3">
                            <div class="position-relative">
                                <input id="password"
                                    type="password"
                                    class="form-control @error('password') is-invalid @enderror"
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

                        <div class="row mb-5">
                            <div class="col pe-0 d-flex align-items-center">
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
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
