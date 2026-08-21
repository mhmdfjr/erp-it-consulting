<x-app-layout>
    <x-slot name="header">
        Edit Departemen: {{ $department->name }}
    </x-slot>

    <div class="max-w-2xl bg-paper-white border border-border-gray/80 rounded-card p-6 shadow-subtle">
        <div class="border-b border-border-gray/60 pb-4 mb-6">
            <h3 class="text-heading-sm font-semibold text-ink-black">Perbarui Data Departemen</h3>
            <p class="text-caption text-slate-gray mt-0.5">Ubah nama unit divisi kerja dalam struktur perusahaan.</p>
        </div>

        <form method="POST" action="{{ route('hr.departments.update', $department) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <x-input
                name="name"
                label="Nama Departemen / Divisi"
                required
                :value="old('name', $department->name)"
            />

            <div class="flex items-center justify-end gap-3 pt-5 mt-6 border-t border-border-gray/60">
                <x-button variant="secondary" size="md" href="{{ route('hr.departments.index') }}">
                    Batal
                </x-button>
                <x-button variant="primary" size="md" type="submit">
                    Simpan Perubahan
                </x-button>
            </div>
        </form>
    </div>
</x-app-layout>
