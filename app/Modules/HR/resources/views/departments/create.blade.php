<x-app-layout>
    <x-slot name="header">
        Tambah Departemen Baru
    </x-slot>

    <div class="max-w-2xl bg-paper-white border border-border-gray/80 rounded-card p-6 shadow-subtle">
        <div class="border-b border-border-gray/60 pb-4 mb-6">
            <h3 class="text-heading-sm font-semibold text-ink-black">Informasi Unit Departemen</h3>
            <p class="text-caption text-slate-gray mt-0.5">Daftarkan divisi kerja baru untuk struktur organisasi perusahaan.</p>
        </div>

        <form method="POST" action="{{ route('hr.departments.store') }}" class="space-y-5">
            @csrf

            <x-input
                name="name"
                label="Nama Departemen / Divisi"
                placeholder="Contoh: Teknologi Informasi, Keuangan, Operasional"
                required
                :value="old('name') ?? ''"
            />

            <div class="flex items-center justify-end gap-3 pt-5 mt-6 border-t border-border-gray/60">
                <x-button variant="secondary" size="md" href="{{ route('hr.departments.index') }}">
                    Batal
                </x-button>
                <x-button variant="primary" size="md" type="submit">
                    Simpan Departemen
                </x-button>
            </div>
        </form>
    </div>
</x-app-layout>
