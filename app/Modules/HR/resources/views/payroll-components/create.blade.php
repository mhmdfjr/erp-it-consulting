<x-app-layout>
    <x-slot name="header">Tambah Payroll Component</x-slot>

    <form method="POST" action="{{ route('hr.payroll-components.store') }}" class="max-w-xl space-y-4">
        @csrf

        <x-input name="name" label="Nama Component" required :value="old('name') ?? ''" />

        <div>
            <label class="text-label text-slate-gray block mb-1">Tipe</label>
            <select name="type" class="w-full rounded-input border-border-gray">
                <option value="earning" {{ old('type', 'earning') === 'earning' ? 'selected' : '' }}>Earning (menambah gaji)</option>
                <option value="deduction" {{ old('type') === 'deduction' ? 'selected' : '' }}>Deduction (mengurangi gaji)</option>
            </select>
        </div>

        <div>
            <label class="text-label text-slate-gray block mb-1">Metode Perhitungan</label>
            <select name="calculation_type" class="w-full rounded-input border-border-gray">
                <option value="fixed_amount" {{ old('calculation_type', 'fixed_amount') === 'fixed_amount' ? 'selected' : '' }}>Nominal Tetap</option>
                <option value="percentage_of_base" {{ old('calculation_type') === 'percentage_of_base' ? 'selected' : '' }}>Persentase dari Base Salary</option>
            </select>
            <p class="text-caption text-slate-gray mt-1">
                Nilai nominal/persentase spesifik diisi per employee saat assignment, bukan di sini.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} id="is_active" />
            <label for="is_active" class="text-body-sm">Aktif</label>
        </div>

        <div class="flex gap-2">
            <x-button type="submit" variant="primary">Simpan</x-button>
            <x-button variant="danger" href="{{ route('hr.payroll-components.index') }}">Batal</x-button>
        </div>
    </form>
</x-app-layout>
