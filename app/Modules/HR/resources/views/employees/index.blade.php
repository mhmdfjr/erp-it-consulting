<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h1 class="text-heading font-medium text-ink-black">Manajemen Karyawan</h1>
            @can('hr.employee.create')
                <x-button variant="primary" href="{{ route('hr.employees.create') }}">
                    + Tambah Karyawan
                </x-button>
            @endcan
        </div>
    </x-slot>

    <x-data-table :headers="['Kode', 'Nama', 'Position', 'PTKP', 'Status', 'Aksi']" :empty="$employees->isEmpty()">
        <x-slot name="emptyState">
            <div class="flex flex-col items-center gap-2">
                <p class="text-heading-sm">Belum ada karyawan</p>
                <p class="text-body-sm text-slate-gray">Tambahkan karyawan pertama untuk mulai kelola gaji.</p>
                <x-button variant="primary" href="{{ route('hr.employees.create') }}">+ Tambah Karyawan</x-button>
            </div>
        </x-slot>

        @foreach ($employees as $employee)
            <tr class="hover:bg-mist-gray">
                <td class="px-4 py-3">{{ $employee->employee_code }}</td>
                <td class="px-4 py-3">{{ $employee->full_name }}</td>
                <td class="px-4 py-3">{{ $employee->position->title }} - {{ $employee->position->department->name }}</td>
                <td class="px-4 py-3">{{ $employee->ptkp_status }}</td>
                <td class="px-4 py-3">
                    <x-badge status="{{ $employee->employment_status === 'active' ? 'success' : 'danger' }}">
                        {{ ucfirst($employee->employment_status) }}
                    </x-badge>
                </td>
                <td class="px-4 py-3 text-right">
                    @can('hr.employee.update')
                        <a href="{{ route('hr.employees.edit', $employee) }}" class="text-info hover:opacity-70">
                            <x-lucide-pencil class="w-4 h-4" />
                        </a>
                    @endcan
                </td>
            </tr>
        @endforeach

        <x-slot name="pagination">
            {{ $employees->links() }}
        </x-slot>
    </x-data-table>
</x-app-layout>
