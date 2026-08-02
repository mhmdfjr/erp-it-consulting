<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'ERP') }}@isset($title) - {{ $title }}@endisset</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,450,500,600" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans antialiased bg-mist-gray text-ink-black">
    <div class="flex min-h-screen">
        <x-sidebar />

        <div class="flex flex-1 flex-col">
            <x-topbar />

            @if (isset($header))
                <div class="bg-paper-white border-b border-border-gray px-6 py-4">
                    <h1 class="text-heading font-medium text-ink-black">{{ $header }}</h1>
                </div>
            @endif

            <main class="flex-1 p-6">
                {{ $slot }}
            </main>
        </div>
    </div>

    @livewireScripts
</body>
</html>
