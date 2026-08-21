<x-app-layout>
    <x-slot name="header">
        Edit Jabatan: {{ $position->title }}
    </x-slot>

    <div class="max-w-2xl bg-paper-white border border-border-gray/80 rounded-card p-6 shadow-subtle">
        <div class="border-b border-border-gray/60 pb-4 mb-6">
            <h3 class="text-heading-sm font-semibold text-ink-black">Perbarui Data Jabatan</h3>
            <p class="text-caption text-slate-gray mt-0.5">Ubah nama titel jabatan atau alokasi departemen.</p>
        </div>

        <form method="POST" action="{{ route('hr.positions.update', $position) }}" class="space-y-5">
            @csrf
            @method('PUT')

            {{-- Departemen --}}
            <div>
                <label for="department_id" class="block text-label font-medium text-slate-gray mb-1.5">Departemen / Divisi <span class="text-danger">*</span></label>
                <select
                    name="department_id"
                    id="department_id"
                    required
                    class="w-full rounded-input border bg-paper-white px-3.5 py-2.5 text-body text-ink-black transition focus:ring-0 focus:outline-none focus:shadow-focus-ring {{ $errors->has('department_id') ? 'border-danger focus:border-danger' : 'border-border-gray focus:border-primary' }}"
                >
                    <option value="">-- Pilih Departemen --</option>
                    @foreach ($departments as $department)
                        <option value="{{ $department->id }}" @selected((old('department_id') ?? $position->department_id) == $department->id)>
                            {{ $department->name }}
                        </option>
                    @endforeach
                </select>
                @error('department_id')
                    <p class="mt-1.5 text-caption font-medium text-danger">{{ $message }}</p>
                @enderror
            </div>

            {{-- Title --}}
            <x-input
                name="title"
                label="Nama Posisi / Title Jabatan"
                required
                :value="old('title', $position->title)"
            />

            <div class="flex items-center justify-end gap-3 pt-5 mt-6 border-t border-border-gray/60">
                <x-button variant="secondary" size="md" href="{{ route('hr.positions.index') }}">
                    Batal
                </x-button>
                <x-button variant="primary" size="md" type="submit">
                    Simpan Perubahan
                </x-button>
            </div>
        </form>
    </div>
</x-app-layout>
