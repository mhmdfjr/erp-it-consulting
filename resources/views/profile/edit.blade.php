<x-app-layout>
    <x-slot name="header">
        Pengaturan Profil Akun
    </x-slot>

    <div class="space-y-6">
        {{-- Profile Header Banner Card --}}
        <div class="bg-paper-white border border-border-gray/80 rounded-card p-6 shadow-subtle flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="h-14 w-14 rounded-2xl bg-primary-tint text-primary font-bold flex items-center justify-center text-body-lg shrink-0 shadow-subtle ring-4 ring-fog-white">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </div>
                <div>
                    <h1 class="text-heading font-bold text-ink-black tracking-tight">{{ auth()->user()->name }}</h1>
                    <p class="text-caption text-slate-gray mt-0.5 flex items-center gap-2 font-medium">
                        <span>{{ auth()->user()->email }}</span>
                        <span>•</span>
                        <span class="inline-flex items-center gap-1 text-primary">
                            <x-dynamic-component component="lucide-shield-check" class="w-3.5 h-3.5" />
                            Akun Terverifikasi
                        </span>
                    </p>
                </div>
            </div>

            <div>
                <x-badge status="primary" variant="subtle" class="gap-1.5 px-3 py-1 text-caption">
                    <x-dynamic-component component="lucide-user" class="w-3.5 h-3.5" />
                    <span>Pengguna Sistem</span>
                </x-badge>
            </div>
        </div>

        {{-- Form Split Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
            {{-- Kartu 1: Informasi Profil --}}
            <div class="bg-paper-white border border-border-gray/80 rounded-card p-6 sm:p-8 shadow-subtle">
                @include('profile.partials.update-profile-information-form')
            </div>

            {{-- Kartu 2: Keamanan / Ubah Password --}}
            <div class="bg-paper-white border border-border-gray/80 rounded-card p-6 sm:p-8 shadow-subtle">
                @include('profile.partials.update-password-form')
            </div>
        </div>
    </div>
</x-app-layout>
