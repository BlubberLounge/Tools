@extends('layouts.auth')

@section('content')
<div class="container mx-auto px-4">
    <div class="flex justify-center">
        <div class="w-full max-w-2xl">
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <h2 class="card-title text-base">{{ __('Reset Password') }}</h2>

                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf

                        <div class="md:flex md:items-start mb-3">
                            <label for="email" class="md:w-1/3 label md:justify-end md:pr-4"><span class="label-text">{{ __('Email Address') }}</span></label>

                            <div class="md:w-1/2">
                                <input id="email" type="email" class="input input-bordered w-full @error('email') input-error @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                                @error('email')
                                    <div class="label">
                                        <span class="label-text-alt text-error">{{ $message }}</span>
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <div class="md:flex">
                            <div class="md:w-1/3"></div>
                            <div class="md:w-1/2">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Send Password Reset Link') }}
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
