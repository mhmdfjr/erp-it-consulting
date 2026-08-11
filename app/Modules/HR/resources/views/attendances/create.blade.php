<x-app-layout>
    <x-slot name="header">Catat Attendance</x-slot>

    <form method="POST" action="{{ route('hr.attendances.store') }}" class="max-w-xl space-y-4">
        @csrf

        <div>
            <label class="text-label text-slate-gray block mb-1">Employee</label>
            <select name="employee_id" class="w-full rounded-input border-border-gray">
                @foreach ($employees as $employee)
                    <option value="{{ $employee->id }}" {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                        {{ $employee->full_name }} ({{ $employee->employee_code }})
                    </option>
                @endforeach
            </select>
        </div>

        <x-input name="date" type="date" label="Tanggal" required :value="old('date') ?? ''" />

        <div>
            <label class="text-label text-slate-gray block mb-1">Status</label>
            <select name="status" x-data class="w-full rounded-input border-border-gray">
                <option value="present" {{ old('status', 'present') === 'present' ? 'selected' : '' }}>Present</option>
                <option value="absent" {{ old('status') === 'absent' ? 'selected' : '' }}>Absent</option>
                <option value="leave" {{ old('status') === 'leave' ? 'selected' : '' }}>Leave</option>
                <option value="sick" {{ old('status') === 'sick' ? 'selected' : '' }}>Sick</option>
            </select>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <x-input name="check_in" type="time" label="Check In (opsional)" :value="old('check_in') ?? ''" />
            <x-input name="check_out" type="time" label="Check Out (opsional)" :value="old('check_out') ?? ''" />
        </div>

        <x-input name="note" label="Catatan (opsional)" :value="old('note') ?? ''" />

        <div class="flex gap-2">
            <x-button type="submit" variant="primary">Simpan</x-button>
            <x-button variant="danger" href="{{ route('hr.attendances.index') }}">Batal</x-button>
        </div>
    </form>
</x-app-layout>
