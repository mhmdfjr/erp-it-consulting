<x-app-layout>
    <x-slot name="header">Edit Pengaturan</x-slot>

    <form method="POST" action="{{ route('identity.settings.update', $setting) }}" class="max-w-lg space-y-4">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-label text-slate-gray mb-1">Key</label>
            <p class="text-body font-mono">{{ $setting->key }}</p>
        </div>

        <div>
            <label class="block text-label text-slate-gray mb-1">Value (JSON)</label>
            <textarea name="value" rows="4"
                class="w-full rounded-input border px-3 py-2 text-body font-mono focus:outline-none focus:shadow-focus-ring {{ $errors->has('value') ? 'border-danger' : 'border-border-gray focus:border-ink-black' }}">{{ old('value', json_encode($setting->value)) }}</textarea>
            @error('value')
                <p class="mt-1 text-caption text-danger">{{ $message }}</p>
            @enderror
        </div>

        <x-input name="description" label="Deskripsi" :value="old('description', $setting->description)" />

        <div class="flex justify-end">
            <x-button variant="primary" type="submit">Simpan</x-button>
        </div>
    </form>
</x-app-layout>
