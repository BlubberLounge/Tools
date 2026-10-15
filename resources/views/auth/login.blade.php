@extends('layouts.auth')

@push('scripts')
    @vite(['resources/js/auth.js'])
@endpush

@section('content')
<div class="flex h-full">
    {{-- Left side - Image --}}
    <div class="hidden md:block w-1/2 bg-cover bg-center" style="background-image: url('/storage/img/blubberlounge_enamel_cup_03.jpg');">
        <div class="w-full h-full bg-black/40"></div>
    </div>

    {{-- Right side - Form --}}
    <div class="w-full md:w-1/2 flex items-center justify-center p-10">
        <div class="w-full max-w-md">
            <div class="flex justify-center mb-12">
                <a class="flex items-center" href="{{ url('/') }}">
                    <img src="https://media.blubber-lounge.de/images/blubber_lounge_rebrand_try_white_optimized.svg" alt="BlubberLounge Logo" width="150px">
                </a>
                <div class="divider divider-horizontal mx-4 opacity-30"></div>
                <a class="flex items-center text-base-content/70 text-3xl" href="{{ url('/') }}">
                    <i class="fa-solid fa-screwdriver-wrench" style="font-size: 2rem"></i>
                </a>
            </div>

            <fieldset class="fieldset">
                <form method="POST" action="{{ route('login') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="label">{{ __('Username or Email') }}</label>
                        <label class="input w-full @error('name') input-error @enderror">
                            <i class="fa-solid fa-user opacity-50"></i>
                            <input type="text"
                                id="name"
                                class="grow"
                                name="name"
                                value="{{ old('name') }}"
                                placeholder="Type your Username or Email"
                                required
                                autofocus
                            >
                        </label>
                        @error('email')
                            <p class="label text-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="label">{{ __('Password') }}</label>
                        <label class="input w-full @error('password') input-error @enderror">
                            <input id="password"
                                type="password"
                                class="grow"
                                name="password"
                                placeholder="Type your Password"
                                required
                                autocomplete="current-password"
                            >
                            <i class="fa-solid fa-key opacity-50 cursor-pointer" id="password-toggler"></i>
                        </label>
                        @error('password')
                            <p class="label text-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="label cursor-pointer gap-2">
                            <input type="checkbox" class="checkbox checkbox-sm" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                            {{ __('Remember Me') }}
                        </label>
                        @if (Route::has('password.request'))
                            <a class="link link-hover text-sm" href="{{ route('password.request') }}">
                                {{ __('Forgot Your Password?') }}
                            </a>
                        @endif
                    </div>

                    <button type="submit" class="btn btn-primary w-full mt-2">
                        {{ __('Login') }}
                    </button>
                </form>
            </fieldset>

            @if(Route::has('blubberlounge.redirect'))
                <div class="divider text-base-content/40 my-6">or</div>
                <a href="{{ route('blubberlounge.redirect') }}" class="btn w-full flex items-center justify-center gap-2 border-none text-black hover:brightness-90" style="background-color: #FFB800;">
                    <img src="https://media.blubber-lounge.de/images/bubbles_rect.svg" width="22">
                    Sign in with BlubberLounge
                </a>
            @endif

            <div class="flex justify-center mt-8">
                <span class="text-base-content/60">Don't have an Account?</span>
                <a href="{{ route('register.request') }}" class="link link-primary ml-2">Request Access</a>
            </div>
        </div>
    </div>
</div>
@endsection
