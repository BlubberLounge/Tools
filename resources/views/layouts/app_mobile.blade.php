<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    @include('layouts.meta_social_share')

    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <meta name="description" content="A collection of more or less usefull tools." />

    @vite(['resources/js/app.js', 'resources/js/dark-mode.js'])
    @stack('scripts')

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lipis/flag-icons@6.6.6/css/flag-icons.min.css" />

    @vite(['resources/css/app.css'])
    @stack('styles')
</head>

<body class="custom-scrollbar bg-base-200">

    <div class="drawer lg:drawer-open">
        <input id="app-drawer" type="checkbox" class="drawer-toggle" />

        <!-- Main Content -->
        <div class="drawer-content flex flex-col min-h-screen">
            <!-- Navbar -->
            <div class="navbar bg-base-100 border-b border-base-300 px-4 sticky top-0 z-30">
                <div class="flex-1 gap-2">
                    <label for="app-drawer" class="btn btn-ghost btn-sm btn-square lg:hidden">
                        <i class="fa-solid fa-bars"></i>
                    </label>
                    <div class="text-sm breadcrumbs p-0">
                        {{ Breadcrumbs::render() }}
                    </div>
                </div>
                <div class="flex-none flex items-center gap-2">
                    {{-- Notifications --}}
                    <div class="dropdown dropdown-end" x-data="{ loaded: false }">
                        <div tabindex="0" role="button"
                             class="btn btn-ghost btn-sm btn-circle indicator"
                             @click="if(!loaded) { loaded = true; notification.load({ setContent: (opts) => { $refs.popoverContent.innerHTML = opts['.popover-body'] || ''; } }); }"
                             data-notification-toggle>
                            <i class="fa-solid fa-bell"></i>
                            @if(Auth::user()->unreadNotifications->count() > 0)
                                <span id="notification-counter" class="indicator-item badge badge-error badge-xs">
                                    {{ Auth::user()->unreadNotifications->count() > 99 ? '99+' : Auth::user()->unreadNotifications->count() }}
                                </span>
                            @endif
                        </div>
                        <div tabindex="0" id="notification-popover" class="dropdown-content bg-base-100 rounded-box z-50 w-80 max-w-[calc(100vw-2rem)] shadow-lg border border-base-300 mt-2">
                            <div class="px-4 py-2 border-b border-base-300 flex items-center justify-between">
                                <span class="font-medium text-sm">{{ __('notifications') }}</span>
                                <a href="#" class="btn btn-ghost btn-xs btn-circle"><i class="fa-solid fa-gear text-xs"></i></a>
                            </div>
                            <div x-ref="popoverContent" class="max-h-96 overflow-y-auto custom-scrollbar p-2">
                                <div class="flex flex-col justify-center items-center py-12">
                                    <span class="loading loading-spinner loading-md text-primary mb-3"></span>
                                    <span class="text-base-content/40 text-sm notification-text">{{ __('loading notifications') }}...</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- User Dropdown --}}
                    <div class="dropdown dropdown-end">
                        <div tabindex="0" role="button" class="btn btn-ghost btn-sm gap-2">
                            <div class="avatar">
                                <div class="w-6 rounded-full">
                                    <img src="{{ Auth::user()->img }}" alt="{{ Auth::user()->name }}" />
                                </div>
                            </div>
                            <span class="hidden sm:inline text-sm">{{ Auth::user()->name }}</span>
                            <i class="fa-solid fa-chevron-down text-xs opacity-50"></i>
                        </div>
                        <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box z-50 w-56 p-2 shadow-lg border border-base-300 mt-2">
                            <li class="menu-title text-xs">{{ Auth::user()->full_name }}</li>
                            <li><a href="{{ route('user.settings') }}"><i class="fa-solid fa-gears w-4 text-center"></i>{{ __('settings') }}</a></li>
                            @permission('viewany.device')
                                <li><a href="{{ route('device.index') }}"><i class="fa-solid fa-desktop w-4 text-center"></i>{{ __('devices') }}</a></li>
                            @endpermission
                            @permission('create.feedback')
                                <li><a href="{{ route('feedback.create') }}"><i class="fa-solid fa-circle-question w-4 text-center"></i>{{ __('feedback') }}</a></li>
                            @endpermission
                            @permission('viewany.f.a.q')
                                <li><a href="{{ route('faq.index') }}"><i class="fa-solid fa-comment-dots w-4 text-center"></i>{{ __('tools FAQ') }}</a></li>
                            @endpermission
                            @level(5)
                                <div class="divider my-0"></div>
                                <li class="menu-title text-xs">{{ __('administration') }}</li>
                                <li><a href="{{ route('audit-log.index') }}"><i class="fa-solid fa-list-ul w-4 text-center"></i>{{ __('audit log') }}</a></li>
                                <li><a href="{{ route('invitation.index') }}"><i class="fa-regular fa-envelope w-4 text-center"></i>{{ __('access requests') }}</a></li>
                                <li><a href="{{ route('user.index') }}"><i class="fa-solid fa-users w-4 text-center"></i>{{ __('user management') }}</a></li>
                                <li><a href="{{ route('feedback.index') }}"><i class="fa-solid fa-circle-question w-4 text-center"></i>{{ __('user feedback') }}</a></li>
                                <li><a href="{{ route('l5-swagger.default.api') }}"><i class="fa-solid fa-book w-4 text-center"></i>{{ __('api documentation') }}</a></li>
                            @endlevel
                            <div class="divider my-0"></div>
                            <li>
                                <a href="{{ route('logout') }}" class="text-error" onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                                    <i class="fa-solid fa-right-from-bracket w-4 text-center"></i>{{ __('logout') }}
                                </a>
                            </li>
                        </ul>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
                    </div>
                </div>
            </div>

            {{-- Errors --}}
            @if ($errors->any())
                <div x-data="{ show: true }" x-show="show" class="alert alert-error mx-4 mt-3" role="alert">
                    <div>
                        <ul class="m-0 list-disc list-inside text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <button type="button" @click="show = false" class="btn btn-ghost btn-xs btn-circle">&#x2715;</button>
                </div>
            @endif

            {{-- Page content --}}
            <div class="p-4 flex-1">
                @yield('content')
            </div>
        </div>

        <!-- Sidebar -->
        <div class="drawer-side z-40">
            <label for="app-drawer" aria-label="close sidebar" class="drawer-overlay"></label>
            @include('includes.sidebar')
        </div>
    </div>

</body>
</html>
