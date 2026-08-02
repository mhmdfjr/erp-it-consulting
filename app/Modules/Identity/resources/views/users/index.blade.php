<x-app-layout>
    <x-slot name="header">Pengguna</x-slot>

    <div class="flex justify-between items-center mb-4">
        <h1 class="text-heading font-medium">Pengguna</h1>
        @can('create', \App\Models\User::class)
            <x-button href="{{ route('identity.users.create') }}" variant="primary">+ Tambah Pengguna</x-button>
        @endcan
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-input bg-success-bg text-success px-4 py-2 text-body-sm">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-4 rounded-input bg-danger-bg text-danger px-4 py-2 text-body-sm">{{ session('error') }}</div>
    @endif

    <x-data-table :headers="['Nama', 'Email', 'Role', 'Status', '']" :empty="$users->isEmpty()">
        <x-slot name="emptyState">Belum ada pengguna.</x-slot>

        @foreach ($users as $user)
            <tr class="hover:bg-mist-gray">
                <td class="px-4 py-3 text-body">{{ $user->name }}</td>
                <td class="px-4 py-3 text-body text-slate-gray">{{ $user->email }}</td>
                <td class="px-4 py-3 space-x-1">
                    @foreach ($user->roles as $role)
                        <x-badge status="info">{{ $role->name }}</x-badge>
                    @endforeach
                </td>
                <td class="px-4 py-3">
                    <x-badge :status="$user->is_active ? 'success' : 'danger'">
                        {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                    </x-badge>
                </td>
                <td class="px-4 py-3 text-right">
                    <a href="{{ route('identity.users.edit', $user) }}" class="text-info text-body-sm mr-3">Edit</a>
                    @if (! $user->is(auth()->user()))
                        <form action="{{ route('identity.users.toggle-active', $user) }}" method="POST" class="inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="text-danger text-body-sm">
                                {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                            </button>
                        </form>
                    @endif
                </td>
            </tr>
        @endforeach
    </x-data-table>

    <div class="mt-4">{{ $users->links() }}</div>
</x-app-layout>
