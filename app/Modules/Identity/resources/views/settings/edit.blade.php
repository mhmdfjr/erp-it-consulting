<x-app-layout>
    <x-slot name="header">
        Edit Pengaturan Sistem
    </x-slot>

    @php
        $hasValueError = $errors->has('value');
    @endphp

    <div class="bg-paper-white border border-border-gray/80 rounded-card p-6 shadow-subtle max-w-2xl">
        <div class="border-b border-border-gray/60 pb-4 mb-6">
            <h3 class="text-heading-sm font-semibold text-ink-black">Ubah Parameter Konfigurasi</h3>
            <p class="text-caption text-slate-gray mt-0.5">
                Pastikan struktur data JSON valid sebelum menyimpan perubahan ke sistem.
            </p>
        </div>

        <form method="POST" action="{{ route('identity.settings.update', $setting) }}" class="space-y-5">
            @csrf
            @method('PUT')

            {{-- Readonly Key Field --}}
            <div>
                <label class="block text-label font-medium text-slate-gray mb-1.5">Kunci Pengaturan (Key)</label>
                <div class="p-3 bg-fog-white border border-border-gray/80 rounded-input flex items-center justify-between">
                    <span class="font-mono text-body-sm font-semibold text-primary">{{ $setting->key }}</span>
                    <span class="text-[11px] font-semibold text-ash-gray uppercase tracking-wider">Read Only</span>
                </div>
            </div>

            {{-- JSON Value Textarea --}}
            <div>
                <label for="value" class="block text-label font-medium text-slate-gray mb-1.5">Nilai Konfigurasi (Format JSON)</label>
                <textarea
                    name="value"
                    id="value"
                    rows="6"
                    placeholder='{"key": "value"}'
                    class="w-full rounded-input border bg-paper-white px-3.5 py-2.5 text-body-sm font-mono text-ink-black placeholder-ash-gray transition-colors focus:ring-0 focus:outline-none focus:shadow-focus-ring {{ $hasValueError ? 'border-danger focus:border-danger' : 'border-border-gray focus:border-primary' }}"
                >{{ old('value', is_string($setting->value) ? $setting->value : json_encode($setting->value, JSON_PRETTY_PRINT)) }}</textarea>
                @error('value')
                    <p class="mt-1.5 text-caption font-medium text-danger">{{ $message }}</p>
                @enderror
            </div>

            {{-- Description Input --}}
            <x-input
                name="description"
                label="Deskripsi Parameter"
                :value="old('description', $setting->description)"
                placeholder="Penjelasan fungsi konfigurasi ini di sistem"
            />

            {{-- Action Buttons --}}
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-border-gray/60">
                <x-button variant="secondary" href="{{ route('identity.settings.index') }}">
                    Batal
                </x-button>
                <x-button variant="primary" type="submit">
                    Simpan Perubahan
                </x-button>
            </div>
        </form>
    </div>
</x-app-layout>
