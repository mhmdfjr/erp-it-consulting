<x-app-layout>
    <x-slot name="header">
        <span class="text-ink-black dark:text-paper-white">Profil Perusahaan</span>
    </x-slot>

    {{-- Alert Notification --}}
    @if (session('success'))
        <div class="mb-6 rounded-input bg-success-bg dark:bg-success/10 border border-success/20 text-success px-4 py-3 text-body-sm flex items-center gap-2 max-w-5xl">
            <x-dynamic-component component="lucide-check-circle-2" class="w-4 h-4 shrink-0" />
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="mb-6 rounded-input bg-danger-bg dark:bg-danger/10 border border-danger/20 text-danger px-4 py-3 text-body-sm flex items-center gap-2 max-w-5xl">
            <x-dynamic-component component="lucide-alert-circle" class="w-4 h-4 shrink-0" />
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div class="max-w-5xl grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        {{-- Left Card: Preview Ringkasan Identitas --}}
        <div class="lg:col-span-4 bg-paper-white dark:bg-[#111c44] border border-border-gray/80 dark:border-border-gray/10 rounded-card p-6 shadow-subtle flex flex-col items-center text-center">
            <div class="w-20 h-20 rounded-card bg-primary/10 text-primary flex items-center justify-center mb-4 shadow-subtle border border-primary/20">
                <x-dynamic-component component="lucide-building-2" class="w-10 h-10 text-primary" />
            </div>

            <h3 class="text-heading-sm font-bold text-ink-black dark:text-paper-white">{{ $profile->name ?? 'Nama Perusahaan' }}</h3>
            <span class="text-caption text-slate-gray dark:text-ash-gray mt-0.5">{{ $profile->email ?? 'email@perusahaan.com' }}</span>

            <div class="w-full border-t border-border-gray/60 dark:border-border-gray/10 my-5 pt-4 text-left space-y-3">
                <div>
                    <span class="text-[10px] font-bold text-ash-gray uppercase tracking-wider block">NPWP Perusahaan</span>
                    <span class="text-caption font-semibold text-ink-black dark:text-paper-white tabular-nums">{{ $profile->npwp ?: '-' }}</span>
                </div>

                <div>
                    <span class="text-[10px] font-bold text-ash-gray uppercase tracking-wider block">Kontak Telepon</span>
                    <span class="text-caption font-semibold text-ink-black dark:text-paper-white tabular-nums">{{ $profile->phone ?: '-' }}</span>
                </div>

                <div>
                    <span class="text-[10px] font-bold text-ash-gray uppercase tracking-wider block">Alamat Operasional</span>
                    <p class="text-caption text-slate-gray dark:text-ash-gray leading-relaxed">{{ $profile->address ?: '-' }}</p>
                </div>
            </div>

            <div class="w-full bg-fog-white dark:bg-paper-white/5 rounded-input p-3 border border-border-gray/60 dark:border-border-gray/10 text-left flex items-start gap-2.5">
                <x-dynamic-component component="lucide-shield-check" class="w-4 h-4 text-primary shrink-0 mt-0.5" />
                <p class="text-[11px] text-slate-gray dark:text-ash-gray leading-tight">
                    Informasi ini digunakan sebagai kop resmi pada invoice, slip gaji, dan dokumen perpajakan.
                </p>
            </div>
        </div>

        {{-- Right Card: Form Edit Data --}}
        <div class="lg:col-span-8 bg-paper-white dark:bg-[#111c44] border border-border-gray/80 dark:border-border-gray/10 rounded-card p-6 shadow-subtle">
            <div class="border-b border-border-gray/60 dark:border-border-gray/10 pb-4 mb-6">
                <h3 class="text-heading-sm font-semibold text-ink-black dark:text-paper-white">Informasi Legal & Operasional</h3>
                <p class="text-caption text-slate-gray dark:text-ash-gray mt-0.5">Perbarui data entitas resmi perusahaan untuk keperluan invoice dan cetak dokumen.</p>
            </div>

            <form method="POST" action="{{ route('identity.company-profile.update') }}" class="space-y-5">
                @csrf
                @method('PUT')

                {{-- Nama Perusahaan --}}
                <x-input
                    name="name"
                    label="Nama Perusahaan (Badan Usaha)"
                    :value="old('name', $profile->name)"
                    placeholder="Contoh: PT Solusi Teknologi Nusantara"
                    required
                />

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- NPWP --}}
                    <x-input
                        name="npwp"
                        label="Nomor NPWP"
                        :value="old('npwp', $profile->npwp)"
                        placeholder="Contoh: 01.234.567.8-901.000"
                    />

                    {{-- Telepon --}}
                    <x-input
                        name="phone"
                        label="No. Telepon Kantor"
                        :value="old('phone', $profile->phone)"
                        placeholder="Contoh: (021) 555-0199"
                    />
                </div>

                {{-- Email Resmi --}}
                <x-input
                    name="email"
                    type="email"
                    label="Email Resmi Perusahaan"
                    :value="old('email', $profile->email)"
                    placeholder="finance@perusahaan.com"
                />

                {{-- Alamat --}}
                <div>
                    <label for="address" class="block text-label font-medium text-slate-gray dark:text-ash-gray mb-1.5">Alamat Kantor Lengkap</label>
                    @php
                        $hasAddressError = $errors->has('address');
                    @endphp

                    <textarea
                        name="address"
                        id="address"
                        rows="3"
                        placeholder="Tuliskan nama jalan, gedung, nomor, kota, dan kode pos"
                        class="w-full rounded-input border bg-paper-white dark:bg-paper-white/5 px-3.5 py-2.5 text-body text-ink-black dark:text-paper-white placeholder-ash-gray dark:placeholder-slate-gray transition-colors focus:ring-0 focus:outline-none focus:shadow-focus-ring {{ $hasAddressError ? 'border-danger focus:border-danger' : 'border-border-gray dark:border-border-gray/20 focus:border-primary' }}"
                    >{{ old('address', $profile->address) }}</textarea>
                    @error('address')
                        <p class="mt-1.5 text-caption font-medium text-danger">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Action Button --}}
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-border-gray/60 dark:border-border-gray/10">
                    <x-button variant="primary" type="submit" size="md">
                        Simpan Perubahan
                    </x-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
