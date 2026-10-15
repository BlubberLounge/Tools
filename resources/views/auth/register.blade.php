@extends('layouts.auth')

@section('content')
<div class="container mx-auto px-4">
    <div class="flex justify-center">
        <div class="w-full max-w-2xl">
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <div class="flex justify-center py-8">
                        <div class="flex justify-center py-3">
                            <a class="flex items-center" href="{{ url('/') }}">
                                <img src="https://media.blubber-lounge.de/images/blubber_lounge_rebrand_try_white_optimized.svg" alt="BlubberLounge Logo" width="150px">
                            </a>
                            <div class="divider divider-horizontal mx-4 opacity-30"></div>
                            <a class="flex items-center text-base-content/70 text-3xl" href="{{ url('/') }}">
                                <i class="fa-solid fa-screwdriver-wrench" style="font-size: 2rem"></i>
                            </a>
                        </div>
                    </div>

                    <div class="px-4 pb-4">
                        <form method="POST" action="{{ route('register') }}">
                            @csrf

                            <div class="flex justify-center text-center text-base-content/60 mb-3">
                                <p class="max-w-md">
                                    Fast geschafft! <br />
                                    Nachdem sie das Formular ausgefüllt haben auf <span class="text-base-content font-medium"> {{ __('register') }} </span> drücken.
                                </p>
                            </div>

                            <div class="md:flex md:items-start mb-3">
                                <label for="name" class="md:w-1/3 label md:justify-end md:pr-4"><span class="label-text">{{ __('Name') }}</span></label>

                                <div class="md:w-1/2">
                                    <div class="relative">
                                        <input id="name" type="text" class="input input-bordered w-full pl-10 @error('name') input-error @enderror" name="name" value="{{ old('name') }}" placeholder="Username" required autocomplete="name" autofocus>
                                        <i class="fa-solid fa-user absolute left-3 top-1/2 -translate-y-1/2 text-base-content/40"></i>
                                    </div>
                                    @error('name')
                                        <div class="label">
                                            <span class="label-text-alt text-error">{{ $message }}</span>
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="md:flex md:items-start mb-2">
                                <label for="firstname" class="md:w-1/3 label md:justify-end md:pr-4"><span class="label-text">{{ __('Firstname') }}</span></label>

                                <div class="md:w-1/2">
                                    <div class="relative">
                                        <input id="firstname" type="text" class="input input-bordered w-full pl-10 @error('firstname') input-error @enderror" name="firstname" value="{{ old('firstname') }}" placeholder="Max" required autocomplete="firstname">
                                        <i class="fa-solid fa-user absolute left-3 top-1/2 -translate-y-1/2 text-base-content/40"></i>
                                    </div>
                                    @error('firstname')
                                        <div class="label">
                                            <span class="label-text-alt text-error">{{ $message }}</span>
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="md:flex md:items-start mb-2">
                                <label for="lastname" class="md:w-1/3 label md:justify-end md:pr-4"><span class="label-text">{{ __('Lastname') }}</span></label>

                                <div class="md:w-1/2">
                                    <div class="relative">
                                        <input id="lastname" type="text" class="input input-bordered w-full pl-10 @error('lastname') input-error @enderror" name="lastname" value="{{ old('lastname') }}" placeholder="Mustermann" required autocomplete="lastname">
                                        <i class="fa-solid fa-user absolute left-3 top-1/2 -translate-y-1/2 text-base-content/40"></i>
                                    </div>
                                    @error('lastname')
                                        <div class="label">
                                            <span class="label-text-alt text-error">{{ $message }}</span>
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="md:flex md:items-start mb-3">
                                <label for="email" class="md:w-1/3 label md:justify-end md:pr-4"><span class="label-text">{{ __('Email Address') }}</span></label>

                                <div class="md:w-1/2">
                                    <div class="relative">
                                        <input id="email" type="email" class="input input-bordered w-full pl-10 @error('email') input-error @enderror" name="email" value="{{ old('email') }}" placeholder="muster.max@gmail.com" required autocomplete="email">
                                        <i class="fa-solid fa-at absolute left-3 top-1/2 -translate-y-1/2 text-base-content/40"></i>
                                    </div>
                                    @error('email')
                                        <div class="label">
                                            <span class="label-text-alt text-error">{{ $message }}</span>
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="md:flex md:items-start mb-3">
                                <label for="dob" class="md:w-1/3 label md:justify-end md:pr-4"><span class="label-text">{{ __('Date of Birth') }}</span></label>

                                <div class="md:w-1/2">
                                    <input id="dob" type="date" class="input input-bordered w-full @error('dob') input-error @enderror" name="dob" value="{{ old('dob') }}" required autocomplete="off">
                                    @error('dob')
                                        <div class="label">
                                            <span class="label-text-alt text-error">{{ $message }}</span>
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="md:flex md:items-start mb-3">
                                <label for="password" class="md:w-1/3 label md:justify-end md:pr-4"><span class="label-text">{{ __('Password') }}</span></label>

                                <div class="md:w-1/2">
                                    <div class="relative">
                                        <input id="password" type="password" class="input input-bordered w-full pl-10 @error('password') input-error @enderror" name="password" required autocomplete="new-password">
                                        <i class="fa-solid fa-key absolute right-3 top-1/2 -translate-y-1/2 text-base-content/40 cursor-pointer" id="password-toggler"></i>
                                    </div>
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
                                    <div class="relative">
                                        <input id="password-confirm" type="password" class="input input-bordered w-full pl-10" name="password_confirmation" required autocomplete="new-password">
                                        <i class="fa-solid fa-key absolute right-3 top-1/2 -translate-y-1/2 text-base-content/40 cursor-pointer" id="password-toggler"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="md:flex">
                                <div class="md:w-1/3"></div>
                                <div class="md:w-1/2">
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
</div>
@endsection
