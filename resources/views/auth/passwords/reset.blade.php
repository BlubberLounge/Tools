@extends('layouts.auth')

@section('content')
<div class="container mx-auto px-4">
    <div class="flex justify-center">
        <div class="w-full max-w-2xl">
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <h2 class="card-title text-base">{{ __('Reset Password') }}</h2>

                    <form method="POST" action="{{ route('password.update') }}">
                        @csrf

                        <input type="hidden" name="token" value="{{ $token }}">

                        <div class="md:flex md:items-start mb-3">
                            <label for="email" class="md:w-1/3 label md:justify-end md:pr-4"><span class="label-text">{{ __('Email Address') }}</span></label>

                            <div class="md:w-1/2">
                                <input id="email" type="email" class="input input-bordered w-full @error('email') input-error @enderror" name="email" value="{{ $email ?? old('email') }}" required autocomplete="email" autofocus>
                                @error('email')
                                    <div class="label">
                                        <span class="label-text-alt text-error">{{ $message }}</span>
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <div class="md:flex md:items-start mb-3">
                            <label for="password" class="md:w-1/3 label md:justify-end md:pr-4"><span class="label-text">{{ __('Password') }}</span></label>

                            <div class="md:w-1/2">
                                <input id="password" type="password" class="input input-bordered w-full @error('password') input-error @enderror" name="password" required autocomplete="new-password">
                                @error('password')
                                    <div class="label">
                                        <span class="label-text-alt text-error">{{ $message }}</span>
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <div class="md:flex md:items-start mb-3">
                            <label for="password-confirm" class="md:w-1/3 label md:justify-end md:pr-4"><span class="label-text">{{ __('Confirm Password') }}</span></label>

                            <div class="md:w-1/2">
                                <input id="password-confirm" type="password" class="input input-bordered w-full" name="password_confirmation" required autocomplete="new-password">
                            </div>
                        </div>

                        <div class="md:flex">
                            <div class="md:w-1/3"></div>
                            <div class="md:w-1/2">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Reset Password') }}
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
