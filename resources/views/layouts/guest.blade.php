<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'ERP') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,450,500,600" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-mist-gray text-ink-black">
    <div class="min-h-screen flex flex-col justify-center items-center px-4">
        <a href="/" class="mb-6">
            <x-application-logo class="h-10 w-auto text-ink-black" />
        </a>

        <div class="w-full sm:max-w-md bg-paper-white border border-border-gray shadow-elevated rounded-card px-6 py-6">
            {{ $slot }}
        </div>
    </div>
</body>
</html>
