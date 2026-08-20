<x-app-layout>
    <x-slot name="header">
        Pengaturan Sistem
    </x-slot>

    {{-- Alert Messages --}}
    @if (session('success'))
        <div class="mb-5 rounded-input bg-success-bg border border-success/20 text-success px-4 py-3 text-body-sm flex items-center gap-2 max-w-full">
            <x-dynamic-component component="lucide-check-circle-2" class="w-4 h-4 shrink-0" />
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="mb-5 rounded-input bg-danger-bg border border-danger/20 text-danger px-4 py-3 text-body-sm flex items-center gap-2 max-w-full">
            <x-dynamic-component component="lucide-alert-circle" class="w-4 h-4 shrink-0" />
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Settings Table --}}
    <x-data-table
        title="Konfigurasi & Parameter Sistem"
        subtitle="Daftar variabel kunci konfigurasi global aplikasi ERP"
        :headers="['Kunci Pengaturan (Key)', 'Deskripsi Parameter', 'Tipe Nilai', 'Aksi']"
        :empty="$settings->isEmpty()"
    >
        <x-slot name="emptyState">
            <div class="flex flex-col items-center justify-center py-12 text-center">
                <div class="w-12 h-12 rounded-full bg-primary-tint text-primary flex items-center justify-center mb-3">
                    <x-dynamic-component component="lucide-sliders" class="w-6 h-6" />
                </div>
                <h4 class="text-body-lg font-semibold text-ink-black">Belum Ada Pengaturan</h4>
                <p class="text-caption text-slate-gray mt-1 max-w-sm">Data parameter sistem belum tersedia di basis data.</p>
            </div>
        </x-slot>

        @foreach ($settings as $setting)
            <tr class="hover:bg-mist-gray/40 transition-colors">
                {{-- Key Name with Monospace Chip --}}
                <td class="px-6 py-4">
                    <div class="flex items-center gap-2.5">
                        <div class="h-8 w-8 rounded-card bg-fog-white border border-border-gray/80 text-primary flex items-center justify-center shrink-0">
                            <x-dynamic-component component="lucide-settings-2" class="w-4 h-4" />
                        </div>
                        <span class="font-mono text-body-sm font-semibold text-primary bg-primary-tint/60 px-2.5 py-1 rounded-input border border-primary/20">
                            {{ $setting->key }}
                        </span>
                    </div>
                </td>

                {{-- Description --}}
                <td class="px-6 py-4">
                    <p class="text-body-sm text-ink-black font-medium leading-snug">
                        {{ $setting->description ?: 'Tidak ada deskripsi' }}
                    </p>
                </td>

                {{-- Value Format Type --}}
                <td class="px-6 py-4">
                    <span class="inline-flex items-center gap-1.5 text-caption font-semibold text-slate-gray uppercase tracking-wider bg-fog-white border border-border-gray/80 px-2.5 py-1 rounded-input">
                        <x-dynamic-component component="lucide-code-2" class="w-3.5 h-3.5 text-ash-gray" />
                        JSON / Object
                    </span>
                </td>

                {{-- Action --}}
                <td class="px-6 py-4 text-right">
                    <div class="flex items-center justify-end">
                        <a href="{{ route('identity.settings.edit', $setting) }}"
                           class="p-1.5 rounded-input text-slate-gray hover:text-primary hover:bg-mist-gray transition"
                           title="Ubah Konfigurasi">
                            <x-dynamic-component component="lucide-pencil" class="w-4 h-4" />
                        </a>
                    </div>
                </td>
            </tr>
        @endforeach

        <x-slot name="pagination">
            {{ $settings->links() }}
        </x-slot>
    </x-data-table>
</x-app-layout>
