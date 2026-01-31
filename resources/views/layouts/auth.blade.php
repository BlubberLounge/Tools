<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="dark">
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

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">

    <style>
        body {
            min-height: 100vh;
            background-color: var(--bs-tertiary-bg);
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
            border-radius: var(--bs-border-radius) 0 0 var(--bs-border-radius);
        }
        .vertical-divider {
            margin: 0 1rem;
            padding: 0;
            border-left: var(--bs-border-width) solid;
            opacity: 0.5;
        }
        .nav-brand-sub {
            position: relative;
            align-items: center;
            font-size: 1.7rem;
            color: #fff;
        }
        .nav-brand-sub span {
            font-family: 'Montserrat';
            font-weight: 900;
            line-height: .8;
        }
        .form-control.hasIcon {
            padding-left: 2.5rem;
        }
        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--bs-secondary-color);
        }
        #password-toggler {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--bs-secondary-color);
            cursor: pointer;
        }
        .registerText {
            color: var(--bs-secondary-color);
        }
        .btn-bl-brand {
            background-color: #f97316;
            color: #fff;
            border: none;
        }
        .btn-bl-brand:hover {
            background-color: #ea580c;
            color: #fff;
        }
    </style>
</head>

<body>
    <div id="app">

        <main class="py-3">
            @yield('content')
        </main>

    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
</body>
</html>
