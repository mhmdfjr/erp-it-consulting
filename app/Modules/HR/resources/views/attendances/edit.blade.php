<x-app-layout>
    <x-slot name="header">
        Edit Kehadiran: {{ $attendance->employee?->full_name }}
    </x-slot>

    <div class="max-w-2xl bg-paper-white border border-border-gray/80 rounded-card p-6 shadow-subtle">
        <div class="border-b border-border-gray/60 pb-4 mb-6">
            <h3 class="text-heading-sm font-semibold text-ink-black">Perbarui Data Presensi</h3>
            <p class="text-caption text-slate-gray mt-0.5">Ubah status kehadiran atau koreksi jam masuk & jam pulang karyawan.</p>
        </div>

        {{-- Context Information Box --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-4 bg-fog-white/60 border border-border-gray/60 rounded-input mb-6">
            <div>
                <span class="text-[10px] font-bold text-ash-gray uppercase tracking-wider block">Karyawan Terkait</span>
                <p class="text-body-sm font-semibold text-ink-black mt-0.5">{{ $attendance->employee?->full_name }}</p>
                <span class="font-mono text-caption text-slate-gray">{{ $attendance->employee?->employee_code }}</span>
            </div>

            <div>
                <span class="text-[10px] font-bold text-ash-gray uppercase tracking-wider block">Tanggal Presensi</span>
                <p class="text-body-sm font-semibold text-ink-black mt-0.5 tabular-nums">{{ $attendance->date ? $attendance->date->format('d F Y') : '-' }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('hr.attendances.update', $attendance) }}" class="space-y-5">
            @csrf
            @method('PUT')

            {{-- Status --}}
            <div>
                <label for="status" class="block text-label font-medium text-slate-gray mb-1.5">Status Kehadiran <span class="text-danger">*</span></label>
                <select
                    name="status"
                    id="status"
                    class="w-full rounded-input border border-border-gray bg-paper-white px-3.5 py-2.5 text-body text-ink-black transition focus:ring-0 focus:outline-none focus:border-primary focus:shadow-focus-ring"
                >
                    <option value="present" @selected((old('status') ?? $attendance->status) === 'present')>Present (Hadir)</option>
                    <option value="absent" @selected((old('status') ?? $attendance->status) === 'absent')>Absent (Alfa / Tanpa Keterangan)</option>
                    <option value="leave" @selected((old('status') ?? $attendance->status) === 'leave')>Leave (Cuti)</option>
                    <option value="sick" @selected((old('status') ?? $attendance->status) === 'sick')>Sick (Sakit)</option>
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Check In --}}
                <x-input
                    name="check_in"
                    type="time"
                    label="Waktu Masuk (Check In)"
                    :value="old('check_in', $attendance->check_in)"
                />

                {{-- Check Out --}}
                <x-input
                    name="check_out"
                    type="time"
                    label="Waktu Pulang (Check Out)"
                    :value="old('check_out', $attendance->check_out)"
                />
            </div>

            {{-- Note --}}
            <x-input
                name="note"
                label="Catatan / Keterangan (Opsional)"
                :value="old('note', $attendance->note)"
            />

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3 pt-5 mt-6 border-t border-border-gray/60">
                <x-button variant="secondary" size="md" href="{{ route('hr.attendances.index') }}">
                    Batal
                </x-button>
                <x-button variant="primary" size="md" type="submit">
                    Simpan Perubahan
                </x-button>
            </div>
        </form>
    </div>
</x-app-layout>
