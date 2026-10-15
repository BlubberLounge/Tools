<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" >
<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> {{-- , user-scalable=no --}}

    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">

    <!-- prefetch important images -->
    {{-- <link rel="preload"
        href="https://media.blubber-lounge.de/images/blubber_lounge_rebrand_try_white_optimized.svg"
        as="image"
        type="image/svg+xml" /> --}}

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js" integrity="sha256-oP6HI9z1XaZNBrJURtCoUT5SUnxFr8s3BzRl+cbzUq8=" crossorigin="anonymous"></script>
    @vite(['resources/js/app.js', 'resources/js/dark-mode.js'])
    @stack('scripts')   {{-- get all scripts that got pushed to the stack --}}

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    {{-- <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet"> --}}
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Styles -->
    <!-- DaisyUI themes handle all base styling -->
    @vite(['resources/css/app.css', 'resources/sass/dart.scss'])
    @stack('styles')   {{-- get all styles that got pushed to the stack --}}
</head>

<body>
    <div id="app">
        @if (session('status'))
            <x-alert type="primary" message="{{ session('status') }}" />
        @elseif (session('error'))
            <x-alert type="danger" message="{{ session('error') }}" />
        @elseif (session('success'))
            <x-alert type="success" message="{{ session('success') }}" />
        @elseif (session('info'))
            <x-alert type="info" message="{{ session('info') }}" />
        @endif

        {{-- Validation error debugging --}}
        @if ($errors->any())
            <div class="alert alert-error">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <main> {{-- class="pt-3" --}}
            @yield('content')
        </main>

    </div>
</body>
</html>
