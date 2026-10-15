@extends('layouts.auth')

@section('content')
<div class="container mx-auto px-4 mt-3 md:mt-5 md:pt-4">
    <div class="flex justify-center">
        <div class="w-full max-w-lg">
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <div class="flex justify-center py-8">
                        <div class="flex justify-center my-2">
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
                        <form method="POST" action="{{ route('register.request.store') }}">
                            @csrf
                            @method('PUT')

                            <div class="text-base-content/60 mb-4">
                                <p>
                                    Um den Zugang zu dieser Applikation zu beantragen trage deinen {{ __('firstname') }}, {{ __('lastname') }} und {{ __('email') }} in die Felder ein. Um den Antrag zu verschicken auf <span class="text-base-content font-medium">{{ __('Send Access Request') }}</span> drücken. <br>
                                    Die durchschnittliche Bearbeitungszeit beträgt: <span class="text-base-content font-medium">{{ now()->diffForHumans(now()->addWeeks(4), true) }}</span>.
                                </p>
                            </div>

                            <label class="form-control w-full mb-3">
                                <div class="label">
                                    <span class="label-text">{{ __('firstname') }}</span>
                                </div>
                                <input type="text"
                                    id="firstname"
                                    class="input input-bordered w-full @error('firstname') input-error @enderror"
                                    name="firstname"
                                    value="{{ old('firstname') }}"
                                    placeholder="{{ __('firstname') }}"
                                    required
                                >
                                @error('firstname')
                                    <div class="label">
                                        <span class="label-text-alt text-error">{{ $message }}</span>
                                    </div>
                                @enderror
                            </label>

                            <label class="form-control w-full mb-3">
                                <div class="label">
                                    <span class="label-text">{{ __('lastname') }}</span>
                                </div>
                                <input type="text"
                                    id="lastname"
                                    class="input input-bordered w-full @error('lastname') input-error @enderror"
                                    name="lastname"
                                    value="{{ old('lastname') }}"
                                    placeholder="{{ __('lastname') }}"
                                    required
                                >
                                @error('lastname')
                                    <div class="label">
                                        <span class="label-text-alt text-error">{{ $message }}</span>
                                    </div>
                                @enderror
                            </label>

                            <label class="form-control w-full mb-3">
                                <div class="label">
                                    <span class="label-text">{{ __('email') }}</span>
                                </div>
                                <input type="email"
                                    id="email"
                                    class="input input-bordered w-full @error('email') input-error @enderror"
                                    name="email"
                                    value="{{ old('email') }}"
                                    placeholder="{{ __('email') }}"
                                    required
                                    autofocus
                                >
                                @error('email')
                                    <div class="label">
                                        <span class="label-text-alt text-error">{{ $message }}</span>
                                    </div>
                                @enderror
                            </label>

                            <div>
                                <div class="grid">
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
</div>
@endsection
