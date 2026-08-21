<x-app-layout>
    <x-slot name="header">
        Catat Kehadiran Karyawan
    </x-slot>

    <div class="max-w-2xl bg-paper-white border border-border-gray/80 rounded-card p-6 shadow-subtle">
        <div class="border-b border-border-gray/60 pb-4 mb-6">
            <h3 class="text-heading-sm font-semibold text-ink-black">Formulir Presensi Manual</h3>
            <p class="text-caption text-slate-gray mt-0.5">Catat status kehadiran dan jam check-in / check-out karyawan.</p>
        </div>

        <form method="POST" action="{{ route('hr.attendances.store') }}" class="space-y-5">
            @csrf

            {{-- Employee Picker --}}
            <div>
                <label for="employee_id" class="block text-label font-medium text-slate-gray mb-1.5">Pilih Karyawan <span class="text-danger">*</span></label>
                <select
                    name="employee_id"
                    id="employee_id"
                    required
                    class="w-full rounded-input border bg-paper-white px-3.5 py-2.5 text-body text-ink-black transition focus:ring-0 focus:outline-none focus:shadow-focus-ring {{ $errors->has('employee_id') ? 'border-danger focus:border-danger' : 'border-border-gray focus:border-primary' }}"
                >
                    <option value="">-- Pilih Karyawan --</option>
                    @foreach ($employees as $employee)
                        <option value="{{ $employee->id }}" @selected(old('employee_id') == $employee->id)>
                            {{ $employee->full_name }} ({{ $employee->employee_code }}) - {{ $employee->position?->title ?? '-' }}
                        </option>
                    @endforeach
                </select>
                @error('employee_id')
                    <p class="mt-1.5 text-caption font-medium text-danger">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Tanggal --}}
                <x-input
                    name="date"
                    type="date"
                    label="Tanggal Presensi"
                    required
                    :value="old('date', date('Y-m-d'))"
                />

                {{-- Status --}}
                <div>
                    <label for="status" class="block text-label font-medium text-slate-gray mb-1.5">Status Kehadiran <span class="text-danger">*</span></label>
                    <select
                        name="status"
                        id="status"
                        class="w-full rounded-input border border-border-gray bg-paper-white px-3.5 py-2.5 text-body text-ink-black transition focus:ring-0 focus:outline-none focus:border-primary focus:shadow-focus-ring"
                    >
                        <option value="present" @selected(old('status', 'present') === 'present')>Present (Hadir)</option>
                        <option value="absent" @selected(old('status') === 'absent')>Absent (Alfa / Tanpa Keterangan)</option>
                        <option value="leave" @selected(old('status') === 'leave')>Leave (Cuti)</option>
                        <option value="sick" @selected(old('status') === 'sick')>Sick (Sakit)</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Check In --}}
                <x-input
                    name="check_in"
                    type="time"
                    label="Waktu Masuk (Check In)"
                    :value="old('check_in') ?? ''"
                />

                {{-- Check Out --}}
                <x-input
                    name="check_out"
                    type="time"
                    label="Waktu Pulang (Check Out)"
                    :value="old('check_out') ?? ''"
                />
            </div>

            {{-- Note --}}
            <x-input
                name="note"
                label="Catatan / Keterangan (Opsional)"
                placeholder="Contoh: Izin terlambat, surat dokter terlampir"
                :value="old('note') ?? ''"
            />

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3 pt-5 mt-6 border-t border-border-gray/60">
                <x-button variant="secondary" size="md" href="{{ route('hr.attendances.index') }}">
                    Batal
                </x-button>
                <x-button variant="primary" size="md" type="submit">
                    Simpan Kehadiran
                </x-button>
            </div>
        </form>
    </div>
</x-app-layout>
