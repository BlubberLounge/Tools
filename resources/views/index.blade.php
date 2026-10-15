<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>

    @vite(['resources/js/dark-mode.js', 'resources/css/app.css'])

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body class="bg-base-200 min-h-screen flex items-center justify-center">

    <div class="background-test relative flex flex-col items-center justify-center min-h-screen w-full py-4">
        @if (Route::has('login'))
            <div class="fixed top-0 right-0 px-6 py-4">
                @auth
                    <a href="{{ url('/home') }}" class="link link-hover text-sm">Home</a>
                @else
                    <a href="{{ route('login') }}" class="link link-hover text-sm">Log in</a>
                @endauth
            </div>
        @endif

        <div class="max-w-6xl mx-auto text-center">
            <a href="{{ url('/') }}">
                <img src="https://media.blubber-lounge.de/images/blubber_lounge_rebrand_try_white_optimized.svg" alt="BlubberLounge Logo" width="300px" class="mx-auto">
            </a>

            <div class="flex items-center justify-center gap-6 mt-6">
                <a href="https://github.com/BlubberLounge/Dart-WebApp" class="link link-hover text-sm">
                    <i class="fa-brands fa-github mr-1"></i> GitHub
                </a>
                <a href="https://blubber-lounge.de/" class="link link-hover text-sm">
                    BlubberLounge
                </a>
            </div>

            <p class="mt-3 text-xs text-base-content/40">
                <i class="fa-regular fa-copyright fa-xs"></i> BlubberLounge 2015-{{ now()->format('Y') }}. Some Rights Reserved.
            </p>
        </div>
    </div>

</body>
</html>
