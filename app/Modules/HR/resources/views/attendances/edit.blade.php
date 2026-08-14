<x-app-layout>
    <x-slot name="header">Edit Kehadiran</x-slot>

    <form method="POST" action="{{ route('hr.attendances.update', $attendance) }}" class="max-w-xl space-y-4">
        @csrf
        @method('PUT')

        <div>
            <label class="text-label text-slate-gray block mb-1">Employee</label>
            <p class="text-body py-2">{{ $attendance->employee->full_name }} ({{ $attendance->employee->employee_code }})</p>
        </div>

        <div>
            <label class="text-label text-slate-gray block mb-1">Tanggal</label>
            <p class="text-body py-2">{{ $attendance->date->format('d M Y') }}</p>
        </div>

        <div>
            <label class="text-label text-slate-gray block mb-1">Status</label>
            <select name="status" class="w-full rounded-input border-border-gray">
                <option value="present" {{ (old('status') ?? $attendance->status) === 'present' ? 'selected' : '' }}>Present</option>
                <option value="absent" {{ (old('status') ?? $attendance->status) === 'absent' ? 'selected' : '' }}>Absent</option>
                <option value="leave" {{ (old('status') ?? $attendance->status) === 'leave' ? 'selected' : '' }}>Leave</option>
                <option value="sick" {{ (old('status') ?? $attendance->status) === 'sick' ? 'selected' : '' }}>Sick</option>
            </select>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <x-input name="check_in" type="time" label="Check In (opsional)" :value="old('check_in') ?? $attendance->check_in" />
            <x-input name="check_out" type="time" label="Check Out (opsional)" :value="old('check_out') ?? $attendance->check_out" />
        </div>

        <x-input name="note" label="Catatan (opsional)" :value="old('note') ?? $attendance->note" />

        <div class="flex gap-2">
            <x-button type="submit" variant="primary">Simpan</x-button>
            <x-button variant="danger" href="{{ route('hr.attendances.index') }}">Batal</x-button>
        </div>
    </form>
</x-app-layout>
