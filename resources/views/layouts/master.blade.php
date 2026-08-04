<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>@yield('title', config('app.name'))</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('after-styles')
    </head>
    @stack('after-scripts')
    <body class="flex min-h-screen flex-col bg-white text-gray-900">
        @include('includes.body.header.index')

        <main class="flex-1">
            @yield('content')
        </main>

        @include('includes.body.footer.index')
    </body>
</html>
