@extends('layouts.auth')

@section('content')
<div class="container mx-auto px-4">
    <div class="flex justify-center">
        <div class="w-full max-w-2xl">
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <h2 class="card-title text-base">{{ __('Verify Your Email Address') }}</h2>

                    <div class="mt-2">
                        @if (session('resent'))
                            <div class="alert alert-success" role="alert">
                                {{ __('A fresh verification link has been sent to your email address.') }}
                            </div>
                        @endif

                        <p>
                            {{ __('Before proceeding, please check your email for a verification link.') }}
                            {{ __('If you did not receive the email') }},
                            <form class="inline" method="POST" action="{{ route('verification.resend') }}">
                                @csrf
                                <button type="submit" class="link link-primary">{{ __('click here to request another') }}</button>.
                            </form>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
