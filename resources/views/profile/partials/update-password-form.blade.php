<section>
    <header class="border-b border-border-gray/60 pb-4 mb-6">
        <div class="flex items-center gap-2.5">
            <div class="p-2 rounded-lg bg-primary-tint text-primary">
                <x-dynamic-component component="lucide-key-round" class="w-5 h-5" />
            </div>
            <div>
                <h3 class="text-heading-sm font-semibold text-ink-black">Keamanan & Kata Sandi</h3>
                <p class="text-caption text-slate-gray mt-0.5">Pastikan akun menggunakan kombinasi kata sandi yang aman.</p>
            </div>
        </div>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="space-y-5">
        @csrf
        @method('put')

        {{-- Current Password --}}
        <div>
            <label for="update_password_current_password" class="block text-label font-medium text-slate-gray mb-1.5">Kata Sandi Saat Ini <span class="text-danger">*</span></label>
            <input
                id="update_password_current_password"
                name="current_password"
                type="password"
                autocomplete="current-password"
                placeholder="Masukkan kata sandi saat ini"
                class="w-full rounded-input border bg-paper-white px-3.5 py-2.5 text-body text-ink-black placeholder-ash-gray transition-colors focus:ring-0 focus:outline-none focus:shadow-focus-ring {{ $errors->updatePassword->has('current_password') ? 'border-danger focus:border-danger' : 'border-border-gray focus:border-primary' }}"
            />
            @if ($errors->updatePassword->has('current_password'))
                <p class="mt-1.5 text-caption font-medium text-danger">{{ $errors->updatePassword->first('current_password') }}</p>
            @endif
        </div>

        {{-- New Password --}}
        <div>
            <label for="update_password_password" class="block text-label font-medium text-slate-gray mb-1.5">Kata Sandi Baru <span class="text-danger">*</span></label>
            <input
                id="update_password_password"
                name="password"
                type="password"
                autocomplete="new-password"
                placeholder="Minimal 8 karakter kombinasi"
                class="w-full rounded-input border bg-paper-white px-3.5 py-2.5 text-body text-ink-black placeholder-ash-gray transition-colors focus:ring-0 focus:outline-none focus:shadow-focus-ring {{ $errors->updatePassword->has('password') ? 'border-danger focus:border-danger' : 'border-border-gray focus:border-primary' }}"
            />
            @if ($errors->updatePassword->has('password'))
                <p class="mt-1.5 text-caption font-medium text-danger">{{ $errors->updatePassword->first('password') }}</p>
            @endif
        </div>

        {{-- Confirm Password --}}
        <div>
            <label for="update_password_password_confirmation" class="block text-label font-medium text-slate-gray mb-1.5">Konfirmasi Kata Sandi Baru <span class="text-danger">*</span></label>
            <input
                id="update_password_password_confirmation"
                name="password_confirmation"
                type="password"
                autocomplete="new-password"
                placeholder="Ulangi kata sandi baru"
                class="w-full rounded-input border bg-paper-white px-3.5 py-2.5 text-body text-ink-black placeholder-ash-gray transition-colors focus:ring-0 focus:outline-none focus:shadow-focus-ring {{ $errors->updatePassword->has('password_confirmation') ? 'border-danger focus:border-danger' : 'border-border-gray focus:border-primary' }}"
            />
            @if ($errors->updatePassword->has('password_confirmation'))
                <p class="mt-1.5 text-caption font-medium text-danger">{{ $errors->updatePassword->first('password_confirmation') }}</p>
            @endif
        </div>

        {{-- Form Actions --}}
        <div class="flex items-center justify-between pt-4 border-t border-border-gray/60">
            <div>
                @if (session('status') === 'password-updated')
                    <span
                        x-data="{ show: true }"
                        x-show="show"
                        x-transition
                        x-init="setTimeout(() => show = false, 3000)"
                        class="inline-flex items-center gap-1.5 text-caption font-semibold text-success bg-success-bg border border-success/20 px-3 py-1.5 rounded-input"
                    >
                        <x-dynamic-component component="lucide-check-circle-2" class="w-4 h-4" />
                        Kata sandi berhasil diperbarui!
                    </span>
                @endif
            </div>

            <x-button variant="primary" size="md" type="submit" class="gap-1.5">
                <x-dynamic-component component="lucide-lock" class="w-4 h-4" />
                <span>Perbarui Kata Sandi</span>
            </x-button>
        </div>
    </form>
</section>
