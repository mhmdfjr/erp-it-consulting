<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h1 class="text-heading font-medium text-ink-black">Manajemen Peran & Perizinan</h1>
            @can('create', \Spatie\Permission\Models\Role::class)
                <x-button href="{{ route('identity.roles.create') }}" variant="primary">+ Tambah Peran & Perizinan</x-button>
            @endcan
        </div>
    </x-slot>

    @if (session('success'))
        <div class="mb-4 rounded-input bg-success-bg text-success px-4 py-2 text-body-sm">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-4 rounded-input bg-danger-bg text-danger px-4 py-2 text-body-sm">{{ session('error') }}</div>
    @endif

    <x-data-table :headers="['Nama Role', 'Jumlah Permission', 'Aksi']" :empty="$roles->isEmpty()">
        <x-slot name="emptyState">
            <div class="flex flex-col items-center gap-2">
                <p class="text-heading-sm">Belum ada role</p>
                <p class="text-body-sm text-slate-gray">Tambahkan role pertama ke sistem.</p>
                <x-button variant="primary" href="{{ route('identity.roles.create') }}">+ Tambah Role</x-button>
            </div>
        </x-slot>

        @foreach ($roles as $role)
            <tr class="hover:bg-mist-gray">
                <td class="px-4 py-3 text-body">{{ $role->name }}</td>
                <td class="px-4 py-3 text-body text-slate-gray">{{ $role->permissions_count }}</td>
                <td class="flex items-center justify-start gap-4 py-3">
                    <a href="{{ route('identity.roles.edit', $role) }}" class="text-info hover:opacity-70">
                        <x-lucide-pencil class="w-4 h-4" />
                    </a>
                    @if ($role->name !== 'Super Admin')
                        <form action="{{ route('identity.roles.destroy', $role) }}" method="POST" class="inline" onsubmit="return confirm('Hapus role ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-danger hover:opacity-70">
                                <x-lucide-trash class="w-4 h-4" />
                            </button>
                        </form>
                    @endif
                </td>
            </tr>
        @endforeach

        <x-slot name="pagination">
            {{ $roles->links() }}
        </x-slot>
    </x-data-table>
</x-app-layout>
