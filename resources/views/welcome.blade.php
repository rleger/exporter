<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Welcome</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="flex min-h-screen flex-col items-center justify-center bg-gray-50">
        <img src="{{ asset('images/sheep.png') }}" alt="Sheep" class="mx-auto mb-6 h-80 w-auto" />

        @if (Route::has('login'))
            @auth
                <a href="{{ url('/dashboard') }}" class="text-lg font-bold hover:underline">
                    Entrer
                </a>
            @else
                <a href="{{ route('login') }}" class="text-lg font-bold hover:underline">
                    Se connecter
                </a>
            @endauth
        @endif
    </body>

</html>
