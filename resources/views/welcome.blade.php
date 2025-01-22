<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Welcome</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex flex-col items-center justify-center min-h-screen bg-gray-50 dark:bg-black dark:text-white/50">
  <img src="{{ asset('images/sheep.png') }}" alt="Sheep" class="w-auto mx-auto mb-6 h-80" />

  @if (Route::has('login'))
  @auth
  <a href="{{ url('/dashboard') }}" class="text-lg font-bold hover:underline">
    Dashboard
  </a>
  @else
  <a href="{{ route('login') }}" class="text-lg font-bold hover:underline">
    Connection
  </a>
  @endauth
  @endif
</body>

</html>
