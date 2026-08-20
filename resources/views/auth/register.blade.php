<x-guest-layout>
    <div class="min-h-screen w-full bg-fog-white flex flex-col justify-between p-4 sm:p-6">
        {{-- Floating Top Bar --}}
        <header class="w-full max-w-6xl mx-auto mb-6">
            <div class="bg-paper-white/80 backdrop-blur-md border border-border-gray/80 rounded-badge px-6 py-3 shadow-subtle flex items-center justify-between">
                <div class="flex items-center gap-2.5 font-bold text-ink-black tracking-tight text-body-sm">
                    <div class="h-7 w-7 rounded-input bg-primary text-paper-white flex items-center justify-center shadow-subtle">
                        <x-dynamic-component component="lucide-layers" class="w-4 h-4 text-paper-white" />
                    </div>
                    <span>{{ config('app.name', 'KelolaIn') }}</span>
                </div>

                <nav class="hidden md:flex items-center gap-8 text-caption font-semibold text-slate-gray">
                    <a href="{{ route('dashboard') }}" class="hover:text-primary transition flex items-center gap-1.5">
                        <x-dynamic-component component="lucide-layout-dashboard" class="w-3.5 h-3.5" />
                        Dashboard
                    </a>
                    <a href="{{ route('register') }}" class="text-primary flex items-center gap-1.5 font-bold">
                        <x-dynamic-component component="lucide-user-plus" class="w-3.5 h-3.5" />
                        Sign Up
                    </a>
                    <a href="{{ route('login') }}" class="hover:text-primary transition flex items-center gap-1.5">
                        <x-dynamic-component component="lucide-key-round" class="w-3.5 h-3.5" />
                        Sign In
                    </a>
                </nav>

                <a href="{{ route('login') }}" class="px-5 py-2 rounded-badge bg-ink-black text-paper-white text-caption font-semibold hover:opacity-90 transition">
                    Masuk
                </a>
            </div>
        </header>

        {{-- Split Form Container --}}
        <main class="w-full max-w-6xl mx-auto my-auto grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            {{-- Left Column: Register Form --}}
            <div class="lg:col-span-5 px-4 sm:px-8">
                <div class="mb-6">
                    <h2 class="text-heading-lg font-bold text-primary tracking-tight leading-tight">Create Account</h2>
                    <p class="text-body-sm text-slate-gray mt-1.5">Daftarkan akun untuk mulai mengelola sistem ERP</p>
                </div>

                <form method="POST" action="{{ route('register') }}" class="space-y-3.5">
                    @csrf

                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-caption font-semibold text-ink-black mb-1.5">Nama Lengkap</label>
                        <x-text-input
                            id="name"
                            class="block w-full text-body-sm"
                            type="text"
                            name="name"
                            :value="old('name')"
                            placeholder="John Doe"
                            required
                            autofocus
                            autocomplete="name"
                        />
                        <x-input-error :messages="$errors->get('name')" class="mt-1 text-caption text-danger" />
                    </div>

                    <!-- Email Address -->
                    <div>
                        <label for="email" class="block text-caption font-semibold text-ink-black mb-1.5">Email Perusahaan</label>
                        <x-text-input
                            id="email"
                            class="block w-full text-body-sm"
                            type="email"
                            name="email"
                            :value="old('email')"
                            placeholder="nama@perusahaan.com"
                            required
                            autocomplete="username"
                        />
                        <x-input-error :messages="$errors->get('email')" class="mt-1 text-caption text-danger" />
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-caption font-semibold text-ink-black mb-1.5">Password</label>
                        <x-text-input
                            id="password"
                            class="block w-full text-body-sm"
                            type="password"
                            name="password"
                            placeholder="Minimal 8 karakter"
                            required
                            autocomplete="new-password"
                        />
                        <x-input-error :messages="$errors->get('password')" class="mt-1 text-caption text-danger" />
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label for="password_confirmation" class="block text-caption font-semibold text-ink-black mb-1.5">Konfirmasi Password</label>
                        <x-text-input
                            id="password_confirmation"
                            class="block w-full text-body-sm"
                            type="password"
                            name="password_confirmation"
                            placeholder="Ulangi password"
                            required
                            autocomplete="new-password"
                        />
                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1 text-caption text-danger" />
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-3">
                        <button type="submit" class="w-full rounded-input bg-primary hover:bg-primary-hover text-paper-white py-3 text-body-sm font-bold shadow-subtle transition focus:outline-none focus:shadow-focus-ring uppercase tracking-wider">
                            {{ __('SIGN UP') }}
                        </button>
                    </div>

                    <p class="text-center text-caption text-slate-gray pt-2">
                        Sudah punya akun?
                        <a href="{{ route('login') }}" class="font-semibold text-primary hover:underline">
                            Sign In
                        </a>
                    </p>
                </form>
            </div>

            {{-- Right Column: Modern Building Hero Graphic --}}
            <div class="hidden lg:block lg:col-span-7 h-[620px]">
                <div class="relative w-full h-full rounded-card overflow-hidden shadow-elevated">
                    <!-- Modern Architecture Image -->
                    <img
                        src="https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?q=80&w=1200&auto=format&fit=crop"
                        alt="High Rise Modern Office"
                        class="absolute inset-0 w-full h-full object-cover"
                    />

                    <!-- Violet-Magenta-Pink Gradient Filter -->
                    <div class="absolute inset-0 bg-gradient-to-tr from-primary/95 via-secondary/75 to-accent-pink/40 backdrop-blur-[1px]"></div>

                    <!-- Content Inside Image -->
                    <div class="relative z-10 flex flex-col items-center justify-center h-full p-10 text-center text-paper-white">
                        <div class="w-16 h-16 rounded-full bg-paper-white/20 backdrop-blur-md border border-paper-white/30 flex items-center justify-center mb-6 shadow-elevated">
                            <x-dynamic-component component="lucide-sparkles" class="w-8 h-8 text-paper-white" />
                        </div>
                        <h3 class="text-heading font-bold tracking-tight uppercase">Join the Workspace</h3>
                        <p class="text-body-sm text-paper-white/90 max-w-md mt-3 leading-relaxed">
                            Kolaborasi menyeluruh dengan efisiensi tinggi, pelaporan akurat, dan kontrol data operasional real-time.
                        </p>
                    </div>
                </div>
            </div>
        </main>

        {{-- Footer --}}
        <footer class="w-full text-center text-caption text-ash-gray py-4">
            &copy; 2026 {{ config('app.name', 'KelolaIn') }} System. All rights reserved.
        </footer>
    </div>
</x-guest-layout>
