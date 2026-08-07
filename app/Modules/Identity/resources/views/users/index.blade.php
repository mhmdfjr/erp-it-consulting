<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h1 class="text-heading font-medium text-ink-black">Pengguna</h1>
            @can('create', \App\Models\User::class)
                <x-button href="{{ route('identity.users.create') }}" variant="primary">+ Tambah Pengguna</x-button>
            @endcan
        </div>
    </x-slot>

    @if (session('success'))
        <div class="mb-4 rounded-input bg-success-bg text-success px-4 py-2 text-body-sm">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-4 rounded-input bg-danger-bg text-danger px-4 py-2 text-body-sm">{{ session('error') }}</div>
    @endif

    <x-data-table :headers="['Nama', 'Email', 'Role', 'Status', 'Aksi']" :empty="$users->isEmpty()">
        <x-slot name="emptyState">
            <div class="flex flex-col items-center gap-2">
                <p class="text-heading-sm">Belum ada user</p>
                <p class="text-body-sm text-slate-gray">Tambahkan pengguna pertama ke sistem.</p>
                <x-button variant="primary" href="{{ route('identity.users.create') }}">+ Tambah Pengguna</x-button>
            </div>
        </x-slot>

        @foreach ($users as $user)
            <tr class="hover:bg-mist-gray">
                <td class="px-4 py-3 text-body">{{ $user->name }}</td>
                <td class="px-4 py-3 text-body text-slate-gray">{{ $user->email }}</td>
                <td class="px-4 py-3 space-x-1">
                    @foreach ($user->roles as $role)
                        <x-badge :status="match ($role->name) {
                            'Super Admin' => 'danger',
                            'Finance' => 'success',
                            'Sales' => 'warning',
                            'HRD' => 'info',
                            default => 'secondary'}">
                                {{ $role->name}}
                        </x-badge>
                    @endforeach
                </td>
                <td class="px-4 py-3">
                    <x-badge :status="$user->is_active ? 'success' : 'danger'">
                        {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                    </x-badge>
                </td>
                <td class="flex items-center justify-start gap-4 py-3">
                    <a href="{{ route('identity.users.edit', $user) }}" class="text-info hover:opacity-70">
                        <x-lucide-pencil class="w-4 h-4" />
                    </a>
                    @if (! $user->is(auth()->user()))
                        <form action="{{ route('identity.users.toggle-active', $user) }}" method="POST" class="inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit">
                                @if ($user->is_active)
                                    <x-lucide-user-x class="w-4 h-4 text-danger hover:opacity-70" />
                                @else
                                    <x-lucide-user-check class="w-4 h-4 text-success hover:opacity-70" />
                                @endif
                            </button>
                        </form>
                    @endif
                </td>
            </tr>
        @endforeach

        <x-slot name="pagination">
            {{ $users->links() }}
        </x-slot>
    </x-data-table>
</x-app-layout>
