<x-app-layout>
    <x-slot name="header">
        Tambah Komponen Gaji Baru
    </x-slot>

    <div class="max-w-2xl bg-paper-white border border-border-gray/80 rounded-card p-6 shadow-subtle">
        <div class="border-b border-border-gray/60 pb-4 mb-6">
            <h3 class="text-heading-sm font-semibold text-ink-black">Informasi Komponen Payroll</h3>
            <p class="text-caption text-slate-gray mt-0.5">Definisikan tipe tunjangan atau potongan gaji master.</p>
        </div>

        <form method="POST" action="{{ route('hr.payroll-components.store') }}" class="space-y-5">
            @csrf

            {{-- Nama Component --}}
            <x-input
                name="name"
                label="Nama Komponen Gaji"
                placeholder="Contoh: Tunjangan Transportasi, BPJS Ketenagakerjaan"
                required
                :value="old('name') ?? ''"
            />

            {{-- Tipe Komponen --}}
            <div>
                <label for="type" class="block text-label font-medium text-slate-gray mb-1.5">Tipe Komponen <span class="text-danger">*</span></label>
                <select
                    name="type"
                    id="type"
                    class="w-full rounded-input border border-border-gray bg-paper-white px-3.5 py-2.5 text-body text-ink-black transition focus:ring-0 focus:outline-none focus:border-primary focus:shadow-focus-ring"
                >
                    <option value="earning" @selected(old('type', 'earning') === 'earning')>Earning (Menambah Gaji / Tunjangan)</option>
                    <option value="deduction" @selected(old('type') === 'deduction')>Deduction (Mengurangi Gaji / Potongan)</option>
                </select>
            </div>

            {{-- Metode Perhitungan --}}
            <div>
                <label for="calculation_type" class="block text-label font-medium text-slate-gray mb-1.5">Metode Perhitungan <span class="text-danger">*</span></label>
                <select
                    name="calculation_type"
                    id="calculation_type"
                    class="w-full rounded-input border border-border-gray bg-paper-white px-3.5 py-2.5 text-body text-ink-black transition focus:ring-0 focus:outline-none focus:border-primary focus:shadow-focus-ring"
                >
                    <option value="fixed_amount" @selected(old('calculation_type', 'fixed_amount') === 'fixed_amount')>Nominal Tetap (Nominal Tetap)</option>
                    <option value="percentage_of_base" @selected(old('calculation_type') === 'percentage_of_base')>Persentase dari Base Salary (%)</option>
                </select>

                <div class="mt-2.5 p-3 rounded-card bg-fog-white border border-border-gray/80 flex items-start gap-2">
                    <x-dynamic-component component="lucide-info" class="w-4 h-4 text-primary shrink-0 mt-0.5" />
                    <p class="text-[11px] text-slate-gray leading-relaxed">
                        Besaran nominal rupiah atau persentase spesifik akan ditentukan per individu karyawan pada menu <em>Kelola Komponen Gaji Karyawan</em>.
                    </p>
                </div>
            </div>

            {{-- Status Aktif Checkbox --}}
            <div class="pt-2">
                <label class="inline-flex items-center gap-2.5 cursor-pointer">
                    <input
                        type="checkbox"
                        name="is_active"
                        value="1"
                        {{ old('is_active', true) ? 'checked' : '' }}
                        id="is_active"
                        class="rounded-input border-border-gray text-primary focus:ring-0 focus:outline-none focus:shadow-focus-ring h-4 w-4"
                    />
                    <span class="text-body-sm font-medium text-ink-black select-none">Status Komponen Aktif</span>
                </label>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3 pt-5 mt-6 border-t border-border-gray/60">
                <x-button variant="secondary" size="md" href="{{ route('hr.payroll-components.index') }}">
                    Batal
                </x-button>
                <x-button variant="primary" size="md" type="submit">
                    Simpan Komponen
                </x-button>
            </div>
        </form>
    </div>
</x-app-layout>
