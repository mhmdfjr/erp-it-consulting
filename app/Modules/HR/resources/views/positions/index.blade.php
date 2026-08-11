<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h1 class="text-heading font-medium text-ink-black">Position</h1>
            @can('hr.position.manage')
                <x-button variant="primary" href="{{ route('hr.positions.create') }}">
                    + Tambah Position
                </x-button>
            @endcan
        </div>
    </x-slot>

    <x-data-table :headers="['Title', 'Department', 'Aksi']" :empty="$positions->isEmpty()">
        <x-slot name="emptyState">
            <div class="flex flex-col items-center gap-2">
                <p class="text-heading-sm">Belum ada position</p>
                <p class="text-body-sm text-slate-gray">Tambahkan position pertama untuk department yang sudah ada.</p>
                <x-button variant="primary" href="{{ route('hr.positions.create') }}">+ Tambah Position</x-button>
            </div>
        </x-slot>

        @foreach ($positions as $position)
            <tr class="hover:bg-mist-gray">
                <td class="px-4 py-3">{{ $position->title }}</td>
                <td class="px-4 py-3">{{ $position->department->name }}</td>
                <td class="px-4 py-3 text-right">
                    @can('hr.position.manage')
                        <a href="{{ route('hr.positions.edit', $position) }}" class="text-info hover:opacity-70">
                            <x-lucide-pencil class="w-4 h-4" />
                        </a>
                    @endcan
                </td>
            </tr>
        @endforeach

        <x-slot name="pagination">
            {{ $positions->links() }}
        </x-slot>
    </x-data-table>
</x-app-layout>
