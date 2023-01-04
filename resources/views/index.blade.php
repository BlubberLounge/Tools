<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="dark">
    
    @include('includes.head')
    
    <body class="antialiased">
        <div class="background-test relative flex items-top justify-center min-h-screen bg-gray-100 dark:bg-gray-900 sm:items-center py-4 sm:pt-0">
            @if (Route::has('login'))
                <div class="hidden fixed top-0 right-0 px-6 py-4 sm:block">
                    @auth
                        <a href="{{ url('/home') }}" class="text-sm text-gray-700 dark:text-gray-500 underline">Home</a>
                    @else
                        <a href="{{ route('login') }}" class="text-sm text-gray-700 dark:text-gray-500 underline">Log in</a>

                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="ml-4 text-sm text-gray-700 dark:text-gray-500 underline">Register</a>
                        @endif
                    @endauth
                </div>
            @endif

            <div class="max-w-6xl mx-auto">
                <div class="flex justify-center">
                    <a class="navbar-brand m-0" href="{{ url('/') }}">
                        <img src="http://media.maximilian-mewes.de/project/bl/blubber_lounge_rebrand_try_white.svg" alt="Dart a Web-App Logo" width="300px">
                    </a>
                </div>

                <div class="flex flex-column justify-center mt-4">
                    <div class="flex items-center justify-content-evenly">
                        <div>                     
                            <i class="fa-brands fa-github fa-lg ml-4 -mt-px w-5 text-gray-400"></i>
                            <a href="https://github.com/BlubberLounge/Dart-WebApp" class="underline d-inline">
                                GitHub Repo
                            </a>
                        </div>
                        <div>
                            <img src="http://media.maximilian-mewes.de/project/bl/ora_2.png" width="15px">
                            <a href="https://blubber-lounge.de/" class="underline">
                                Blubber Lounge
                            </a>
                        </div>
                    </div>

                    <div class="mt-1 text-center text-sm text-gray-500">
                        Laravel v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }})
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
