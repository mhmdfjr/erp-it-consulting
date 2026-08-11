<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h1 class="text-heading font-medium text-ink-black">Attendance</h1>
            @can('hr.attendance.manage')
                <x-button variant="primary" href="{{ route('hr.attendances.create') }}">
                    + Catat Attendance
                </x-button>
            @endcan
        </div>
    </x-slot>

    <form method="GET" class="flex gap-2 mb-4">
        <select name="employee_id" class="rounded-input border-border-gray" onchange="this.form.submit()">
            <option value="">-- Semua Employee --</option>
            @foreach ($employees as $employee)
                <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>
                    {{ $employee->full_name }}
                </option>
            @endforeach
        </select>
        <input type="month" name="month" value="{{ request('month') }}" class="rounded-input border-border-gray" onchange="this.form.submit()" />
    </form>

    <x-data-table :headers="['Tanggal', 'Employee', 'Check In', 'Check Out', 'Status', 'Aksi']" :empty="$attendances->isEmpty()">
        <x-slot name="emptyState">
            <div class="flex flex-col items-center gap-2">
                <p class="text-heading-sm">Belum ada record attendance</p>
                <p class="text-body-sm text-slate-gray">Catat kehadiran pertama untuk mulai tracking.</p>
                <x-button variant="primary" href="{{ route('hr.attendances.create') }}">+ Catat Attendance</x-button>
            </div>
        </x-slot>

        @foreach ($attendances as $attendance)
            <tr class="hover:bg-mist-gray">
                <td class="px-4 py-3">{{ $attendance->date->format('d M Y') }}</td>
                <td class="px-4 py-3">{{ $attendance->employee->full_name }}</td>
                <td class="px-4 py-3">{{ $attendance->check_in ?? '-' }}</td>
                <td class="px-4 py-3">{{ $attendance->check_out ?? '-' }}</td>
                <td class="px-4 py-3">
                    <x-badge status="{{ match($attendance->status) {
                        'present' => 'success',
                        'absent' => 'danger',
                        'leave', 'sick' => 'warning',
                    } }}">
                        {{ ucfirst($attendance->status) }}
                    </x-badge>
                </td>
                <td class="px-4 py-3 text-right">
                    @can('hr.attendance.manage')
                        <a href="{{ route('hr.attendances.edit', $attendance) }}" class="text-info hover:opacity-70">
                            <x-lucide-pencil class="w-4 h-4" />
                        </a>
                    @endcan
                </td>
            </tr>
        @endforeach

        <x-slot name="pagination">
            {{ $attendances->links() }}
        </x-slot>
    </x-data-table>
</x-app-layout>
