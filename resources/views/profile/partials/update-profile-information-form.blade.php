<section>
    <header class="border-b border-border-gray/60 pb-4 mb-6">
        <div class="flex items-center gap-2.5">
            <div class="p-2 rounded-lg bg-primary-tint text-primary">
                <x-dynamic-component component="lucide-user" class="w-5 h-5" />
            </div>
            <div>
                <h3 class="text-heading-sm font-semibold text-ink-black">Informasi Profil</h3>
                <p class="text-caption text-slate-gray mt-0.5">Perbarui data identitas pengguna dan alamat email akun.</p>
            </div>
        </div>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="space-y-5">
        @csrf
        @method('patch')

        {{-- Nama --}}
        <div>
            <label for="name" class="block text-label font-medium text-slate-gray mb-1.5">Nama Lengkap <span class="text-danger">*</span></label>
            <input
                id="name"
                name="name"
                type="text"
                value="{{ old('name', $user->name) }}"
                required
                autofocus
                autocomplete="name"
                placeholder="Masukkan nama lengkap Anda"
                class="w-full rounded-input border bg-paper-white px-3.5 py-2.5 text-body text-ink-black placeholder-ash-gray transition-colors focus:ring-0 focus:outline-none focus:shadow-focus-ring {{ $errors->has('name') ? 'border-danger focus:border-danger' : 'border-border-gray focus:border-primary' }}"
            />
            @error('name')
                <p class="mt-1.5 text-caption font-medium text-danger">{{ $message }}</p>
            @enderror
        </div>

        {{-- Email --}}
        <div>
            <label for="email" class="block text-label font-medium text-slate-gray mb-1.5">Alamat Email <span class="text-danger">*</span></label>
            <input
                id="email"
                name="email"
                type="email"
                value="{{ old('email', $user->email) }}"
                required
                autocomplete="username"
                placeholder="nama@perusahaan.com"
                class="w-full rounded-input border bg-paper-white px-3.5 py-2.5 text-body text-ink-black placeholder-ash-gray transition-colors focus:ring-0 focus:outline-none focus:shadow-focus-ring {{ $errors->has('email') ? 'border-danger focus:border-danger' : 'border-border-gray focus:border-primary' }}"
            />
            @error('email')
                <p class="mt-1.5 text-caption font-medium text-danger">{{ $message }}</p>
            @enderror

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-3 p-3 bg-warning-bg border border-warning/30 rounded-input flex items-start gap-2">
                    <x-dynamic-component component="lucide-alert-triangle" class="w-4 h-4 text-warning shrink-0 mt-0.5" />
                    <div>
                        <p class="text-caption text-ink-black">
                            Alamat email Anda belum diverifikasi.
                            <button form="send-verification" class="text-primary font-semibold hover:underline ml-1">
                                Klik di sini untuk kirim ulang tautan verifikasi.
                            </button>
                        </p>

                        @if (session('status') === 'verification-link-sent')
                            <p class="mt-1 text-[11px] font-semibold text-success flex items-center gap-1">
                                <x-dynamic-component component="lucide-check-circle-2" class="w-3.5 h-3.5" />
                                Tautan verifikasi baru telah dikirim ke email Anda.
                            </p>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        {{-- Form Actions --}}
        <div class="flex items-center justify-between pt-4 border-t border-border-gray/60">
            <div>
                @if (session('status') === 'profile-updated')
                    <span
                        x-data="{ show: true }"
                        x-show="show"
                        x-transition
                        x-init="setTimeout(() => show = false, 3000)"
                        class="inline-flex items-center gap-1.5 text-caption font-semibold text-success bg-success-bg border border-success/20 px-3 py-1.5 rounded-input"
                    >
                        <x-dynamic-component component="lucide-check-circle-2" class="w-4 h-4" />
                        Perubahan profil berhasil disimpan!
                    </span>
                @endif
            </div>

            <x-button variant="primary" size="md" type="submit" class="gap-1.5">
                <x-dynamic-component component="lucide-save" class="w-4 h-4" />
                <span>Simpan Profil</span>
            </x-button>
        </div>
    </form>
</section>
