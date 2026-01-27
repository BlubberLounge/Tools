<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    @include('layouts.meta_social_share')

    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>
    <meta name="description" content="A collection of more or less usefull tools." />

    <!-- Scripts -->
    @vite(['resources/js/app.js'])

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">


    <!-- Tailwind CSS -->
    @vite(['resources/css/tailwind.css'])

    <style>
        body {
            min-height: 100vh;
            background-color: var(--tw-body-tertiary-bg);
            display: flex;
        }
        #app {
            width: 100%;
        }
        .card-brand-logo {
            display: flex;
            justify-content: center;
            padding: 2rem 0 2rem 0;
        }
        .overlay-dark {
            position: relative;
        }
        .overlay-dark::after {
            content: "";
            top: 0;
            left: 0;
            position: absolute;
            background-color: rgba(35, 38, 39, .3);
            width: 100%;
            height: 100%;
        }
    </style>
</head>

<body class="custom-scrollbar">
    <div id="app">

        <main class="py-3">
            @yield('content')
        </main>

    </div>
</body>
</html>
