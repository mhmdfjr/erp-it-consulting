<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h1 class="text-heading font-medium text-ink-black">Manajemen Departemen</h1>
            @can('hr.department.manage')
                <x-button variant="primary" href="{{ route('hr.departments.create') }}">
                    + Tambah Departemen
                </x-button>
            @endcan
        </div>
    </x-slot>

    <x-data-table :headers="['Nama', 'Jumlah Position', 'Aksi']" :empty="$departments->isEmpty()">
        <x-slot name="emptyState">
            <div class="flex flex-col items-center gap-2">
                <p class="text-heading-sm">Belum ada department</p>
                <p class="text-body-sm text-slate-gray">Tambahkan department pertama sebelum membuat position.</p>
                <x-button variant="primary" href="{{ route('hr.departments.create') }}">+ Tambah Departemen</x-button>
            </div>
        </x-slot>

        @foreach ($departments as $department)
            <tr class="hover:bg-mist-gray">
                <td class="px-4 py-3">{{ $department->name }}</td>
                <td class="px-4 py-3 tabular-nums">{{ $department->positions_count }}</td>
                <td class="px-4 py-3 text-right">
                    @can('hr.department.manage')
                        <a href="{{ route('hr.departments.edit', $department) }}" class="text-info hover:opacity-70">
                            <x-lucide-pencil class="w-4 h-4" />
                        </a>
                    @endcan
                </td>
            </tr>
        @endforeach

        <x-slot name="pagination">
            {{ $departments->links() }}
        </x-slot>
    </x-data-table>
</x-app-layout>
