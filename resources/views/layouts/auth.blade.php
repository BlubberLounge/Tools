<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    @include('layouts.meta_social_share')

    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>
    <meta name="description" content="A collection of more or less usefull tools." />

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Main CSS (Tailwind) -->
    @vite(['resources/js/dark-mode.js', 'resources/css/app.css'])
    @stack('styles')
</head>

<body class="bg-base-200 h-screen overflow-hidden flex">
    <div id="app" class="w-full h-full">

        <main class="h-full">
            @yield('content')
        </main>

    </div>
    @stack('scripts')
</body>
</html>
