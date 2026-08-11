<x-app-layout>
    <x-slot name="header">Edit Payroll Component</x-slot>

    <form method="POST" action="{{ route('hr.payroll-components.update', $payrollComponent) }}" class="max-w-xl space-y-4">
        @csrf
        @method('PUT')

        <x-input name="name" label="Nama Component" required :value="old('name') ?? $payrollComponent->name" />

        <div>
            <label class="text-label text-slate-gray block mb-1">Tipe</label>
            <select name="type" class="w-full rounded-input border-border-gray">
                <option value="earning" {{ (old('type') ?? $payrollComponent->type) === 'earning' ? 'selected' : '' }}>Earning (menambah gaji)</option>
                <option value="deduction" {{ (old('type') ?? $payrollComponent->type) === 'deduction' ? 'selected' : '' }}>Deduction (mengurangi gaji)</option>
            </select>
        </div>

        <div>
            <label class="text-label text-slate-gray block mb-1">Metode Perhitungan</label>
            <select name="calculation_type" class="w-full rounded-input border-border-gray">
                <option value="fixed_amount" {{ (old('calculation_type') ?? $payrollComponent->calculation_type) === 'fixed_amount' ? 'selected' : '' }}>Nominal Tetap</option>
                <option value="percentage_of_base" {{ (old('calculation_type') ?? $payrollComponent->calculation_type) === 'percentage_of_base' ? 'selected' : '' }}>Persentase dari Base Salary</option>
            </select>
            <p class="text-caption text-warning mt-1">
                Mengubah metode perhitungan TIDAK mengubah assignment employee yang sudah ada (amount/percentage tersimpan di employee_payroll_components). Cek ulang assignment terkait kalau metode diubah.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <input type="checkbox" name="is_active" value="1" {{ (old('is_active') ?? $payrollComponent->is_active) ? 'checked' : '' }} id="is_active" />
            <label for="is_active" class="text-body-sm">Aktif</label>
        </div>

        <div class="flex gap-2">
            <x-button type="submit" variant="primary">Simpan</x-button>
            <x-button variant="danger" href="{{ route('hr.payroll-components.index') }}">Batal</x-button>
        </div>
    </form>
</x-app-layout>
