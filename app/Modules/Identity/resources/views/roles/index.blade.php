<x-app-layout>
    <x-slot name="header">Role & Permission</x-slot>

    <div class="flex justify-between items-center mb-4">
        <h1 class="text-heading font-medium">Role & Permission</h1>
        @can('create', \Spatie\Permission\Models\Role::class)
            <x-button href="{{ route('identity.roles.create') }}" variant="primary">+ Tambah Role</x-button>
        @endcan
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-input bg-success-bg text-success px-4 py-2 text-body-sm">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-4 rounded-input bg-danger-bg text-danger px-4 py-2 text-body-sm">{{ session('error') }}</div>
    @endif

    <x-data-table :headers="['Nama Role', 'Jumlah Permission', '']" :empty="$roles->isEmpty()">
        <x-slot name="emptyState">Belum ada role.</x-slot>

        @foreach ($roles as $role)
            <tr class="hover:bg-mist-gray">
                <td class="px-4 py-3 text-body">{{ $role->name }}</td>
                <td class="px-4 py-3 text-body text-slate-gray">{{ $role->permissions_count }}</td>
                <td class="px-4 py-3 text-right">
                    <a href="{{ route('identity.roles.edit', $role) }}" class="text-info text-body-sm mr-3">Edit</a>
                    @if ($role->name !== 'Super Admin')
                        <form action="{{ route('identity.roles.destroy', $role) }}" method="POST" class="inline" onsubmit="return confirm('Hapus role ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-danger text-body-sm">Hapus</button>
                        </form>
                    @endif
                </td>
            </tr>
        @endforeach
    </x-data-table>

    <div class="mt-4">{{ $roles->links() }}</div>
</x-app-layout>
