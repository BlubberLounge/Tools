@extends('layouts.auth')

@section('content')
<div class="container mx-auto px-4">
    <div class="flex justify-center">
        <div class="w-full max-w-2xl">
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <h2 class="card-title text-base">{{ __('Confirm Password') }}</h2>

                    <p class="text-base-content/70">{{ __('Please confirm your password before continuing.') }}</p>

                    <form method="POST" action="{{ route('password.confirm') }}">
                        @csrf

                        <div class="md:flex md:items-start mb-3 mt-4">
                            <label for="password" class="md:w-1/3 label md:justify-end md:pr-4"><span class="label-text">{{ __('Password') }}</span></label>

                            <div class="md:w-1/2">
                                <input id="password" type="password" class="input input-bordered w-full @error('password') input-error @enderror" name="password" required autocomplete="current-password">
                                @error('password')
                                    <div class="label">
                                        <span class="label-text-alt text-error">{{ $message }}</span>
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <div class="md:flex">
                            <div class="md:w-1/3"></div>
                            <div class="md:w-2/3 flex items-center gap-3">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Confirm Password') }}
                                </button>

                                @if (Route::has('password.request'))
                                    <a class="link link-hover" href="{{ route('password.request') }}">
                                        {{ __('Forgot Your Password?') }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
