<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    @include('layouts.meta_social_share')

    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>
    <meta name="description" content="A collection of more or less usefull tools." />

    <!-- Scripts -->
    @vite(['resources/js/app.js', 'resources/js/dark-mode.js'])
    @stack('scripts')

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Flag icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lipis/flag-icons@6.6.6/css/flag-icons.min.css" />

    <!-- Main CSS -->
    @vite(['resources/css/app.css'])
    @stack('styles')
</head>

<body class="custom-scrollbar">
    <div id="app" class="flex items-stretch">

        @include('includes.sidebar')

        <main class="py-3">
            <div class="w-full flex justify-center">
                <div id="topBar" class="px-3 md:px-4 py-2 bg-[var(--tw-body-tertiary-bg)] rounded">
                    <div class="mr-auto">
                        {{ Breadcrumbs::render() }}
                    </div>
                    <div x-data="{ open: false }" class="relative">
                        <button
                            @click="open = !open; if(open) { notification.load({ setContent: (opts) => { $refs.popoverContent.innerHTML = opts['.popover-body'] || ''; } }); }"
                            class="btn text-white relative"
                            data-notification-toggle
                        >
                            <i class="fa-solid fa-bell fa-xl mode"></i>
                            @if(Auth::user()->unreadNotifications->count() > 0)
                                <span id="notification-counter" class="absolute -translate-y-1/2 -translate-x-1/2 bg-danger border border-gray-800 badge rounded-full text-xs px-1.5 py-0.5" style="left: .4rem;top: .2rem;">
                                    {{ Auth::user()->unreadNotifications->count() > 99 ? '99+' : Auth::user()->unreadNotifications->count() }}
                                </span>
                            @endif
                        </button>
                        <!-- Notification Popover -->
                        <div
                            x-show="open"
                            @click.away="open = false"
                            @close-popover.window="open = false"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            id="notification-popover"
                            class="absolute right-0 mt-2 w-[350px] bg-white dark:bg-gray-800 rounded-lg shadow-xl z-50 overflow-hidden"
                            style="display: none;"
                        >
                            <div class="px-4 py-2 border-b border-[var(--tw-border-color)] flex items-center justify-between">
                                <span class="font-medium">{{ __('notifications') }}</span>
                                <a href="#" class="text-[var(--tw-muted-color)] hover:text-[var(--tw-body-color)]"><i class="fa-solid fa-gear"></i></a>
                            </div>
                            <div x-ref="popoverContent" class="max-h-[450px] overflow-y-auto custom-scrollbar p-2">
                                <div class="flex flex-col justify-center items-center notification-no-container">
                                    <div class="spinner-grow text-secondary mb-3"></div>
                                    <div class="text-muted font-medium notification-text">
                                        {{ __('loading notifications') }}...
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <button id="sidebarCollapse" class="btn text-white">
                            <i class="fa-solid fa-bars fa-xl mode"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Validation error debugging --}}
            @if ($errors->any())
                <div x-data="{ show: true }" x-show="show" class="alert alert-danger alert-dismissible mx-5 mt-3" role="alert">
                    <ul class="m-0 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" @click="show = false" class="btn-close" aria-label="Close"></button>
                </div>
            @endif

            <div id="content" class="p-3">
                @yield('content')
            </div>
        </main>

    </div>
</body>
</html>
