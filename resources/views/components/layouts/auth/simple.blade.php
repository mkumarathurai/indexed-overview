<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} - @yield('title', 'Login')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-white antialiased dark:bg-gray-900">
    <div class="bg-background flex min-h-screen flex-col items-center justify-center gap-6 p-6 md:p-10">
        <div class="flex w-full max-w-sm flex-col gap-2">
            <a href="{{ route('projects.index') }}" class="flex flex-col items-center gap-2 font-medium">
                <h1 class="text-2xl font-bold">{{ config('app.name') }}</h1>
            </a>
            {{ $slot }}
        </div>
    </div>
</body>
</html>
