{{-- resources/views/errors/layout.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title') - {{ config('app.name', 'KelolaIn ERP') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-fog-white text-ink-black min-h-screen flex flex-col items-center justify-center p-6 selection:bg-primary-tint selection:text-primary">

    <div class="w-full max-w-2xl">
        {{-- Card Error --}}
        <div class="bg-paper-white border border-border-gray/80 rounded-card py-8 px-8 shadow-elevated text-center flex flex-col items-center">

            {{-- Icon Badge --}}
            <div class="w-16 h-16 rounded-lg bg-primary-tint text-primary flex items-center justify-center mb-4 shadow-subtle">
                @yield('icon')
            </div>

            {{-- Big Code Heading --}}
            <h1 class="font-mono text-5xl sm:text-6xl font-extrabold text-primary tracking-tight mb-2">
                @yield('code')
            </h1>

            {{-- Title & Message --}}
            <h2 class="text-heading font-bold text-ink-black tracking-tight mb-2">
                @yield('heading')
            </h2>
            <p class="text-body-sm text-slate-gray leading-relaxed mb-8 max-w-sm">
                @yield('message')
            </p>

            {{-- Action Buttons --}}
            <div class="w-full pt-6 border-t border-border-gray/60 flex flex-col sm:flex-row items-center justify-center gap-3">
                <x-button variant="secondary" size="md" onclick="window.history.back()" class="w-full sm:w-auto gap-2 justify-center px-5">
                    <x-dynamic-component component="lucide-arrow-left" class="w-4 h-4" />
                    <span>Kembali</span>
                </x-button>

                <x-button variant="primary" size="md" href="{{ url('/') }}" class="w-full sm:w-auto gap-2 justify-center px-5">
                    <x-dynamic-component component="lucide-home" class="w-4 h-4" />
                    <span>Dashboard Utama</span>
                </x-button>
            </div>
        </div>

        {{-- Footer --}}
        <div class="text-center mt-6">
            <p class="text-caption text-slate-gray font-medium">
                &copy; {{ date('Y') }} {{ config('app.name', 'KelolaIn ERP') }} &middot; Akses Keamanan Terpusat
            </p>
        </div>
    </div>
</body>
</html>
