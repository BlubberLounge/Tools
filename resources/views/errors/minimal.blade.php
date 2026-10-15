<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title')</title>
        @vite(['resources/js/dark-mode.js', 'resources/css/app.css'])
    </head>
    <body class="bg-base-200 min-h-screen flex items-center justify-center">
        <div class="max-w-xl mx-auto px-6">
            <div class="flex items-center justify-center gap-4">
                <div class="text-4xl font-bold text-primary">
                    @yield('code')
                </div>
                <div class="divider divider-horizontal"></div>
                <div class="text-lg text-base-content/60 uppercase tracking-wider">
                    @yield('message')
                </div>
            </div>
        </div>
    </body>
</html>
