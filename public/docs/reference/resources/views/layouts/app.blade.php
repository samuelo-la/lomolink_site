<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="@yield('meta_description', 'Education, training and recruitment consulting.')">
        <title>@yield('title', config('company.name'))</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-white font-sans text-dark-grey antialiased">
        <a
            href="#content"
            class="sr-only z-50 rounded-md bg-gold px-4 py-2 text-navy focus:not-sr-only focus:fixed focus:left-4 focus:top-4"
        >
            Skip to content
        </a>

        @include('partials.navbar')

        <main id="content">
            @yield('content')
        </main>

        @include('partials.footer')
    </body>
</html>
