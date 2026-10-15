<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>@yield('title')</title>

        <!-- Styles -->
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="antialiased bg-base-100 text-base-content/50 font-light h-screen m-0">
        <div class="flex items-center justify-center h-screen relative">
            <div class="text-center">
                <div class="text-4xl p-5">
                    @yield('message')
                </div>
            </div>
        </div>
    </body>
</html>
