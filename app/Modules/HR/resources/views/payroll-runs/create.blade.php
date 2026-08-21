<x-app-layout>
    <x-slot name="header">
        Buat Periode Gaji Baru
    </x-slot>

    <div class="max-w-2xl bg-paper-white border border-border-gray/80 rounded-card p-6 shadow-subtle">
        <div class="border-b border-border-gray/60 pb-4 mb-6">
            <h3 class="text-heading-sm font-semibold text-ink-black">Formulir Inisiasi Penggajian</h3>
            <p class="text-caption text-slate-gray mt-0.5">Tentukan bulan dan tahun siklus penggajian yang akan dijalankan.</p>
        </div>

        <form method="POST" action="{{ route('hr.payroll-runs.store') }}" class="space-y-5">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Bulan --}}
                <div>
                    <label for="period_month" class="block text-label font-medium text-slate-gray mb-1.5">Bulan Periode <span class="text-danger">*</span></label>
                    <select
                        name="period_month"
                        id="period_month"
                        required
                        class="w-full rounded-input border border-border-gray bg-paper-white px-3.5 py-2.5 text-body text-ink-black transition focus:ring-0 focus:outline-none focus:border-primary focus:shadow-focus-ring"
                    >
                        @foreach (range(1, 12) as $month)
                            <option value="{{ $month }}" @selected(old('period_month', now()->month) == $month)>
                                {{ \Carbon\Carbon::create(2000, $month, 1)->translatedFormat('F') }}
                            </option>
                        @endforeach
                    </select>
                    @error('period_month')
                        <p class="mt-1.5 text-caption font-medium text-danger">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Tahun --}}
                <x-input
                    name="period_year"
                    type="number"
                    label="Tahun Periode"
                    placeholder="2026"
                    required
                    :value="old('period_year', now()->year)"
                />
            </div>

            <div class="p-3.5 rounded-card bg-fog-white border border-border-gray/80 flex items-start gap-2.5">
                <x-dynamic-component component="lucide-info" class="w-4 h-4 text-primary shrink-0 mt-0.5" />
                <p class="text-[12px] text-slate-gray leading-relaxed">
                    Setelah periode dibuat dalam status <strong>Draft</strong>, sistem akan memvalidasi kehadiran absensi karyawan sebelum kalkulasi nominal gaji dan PPh 21 dijalankan.
                </p>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3 pt-5 mt-6 border-t border-border-gray/60">
                <x-button variant="secondary" size="md" href="{{ route('hr.payroll-runs.index') }}">
                    Batal
                </x-button>
                <x-button variant="primary" size="md" type="submit">
                    Buat Periode Gaji
                </x-button>
            </div>
        </form>
    </div>
</x-app-layout>
