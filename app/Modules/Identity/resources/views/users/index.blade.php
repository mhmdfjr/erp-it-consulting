<x-app-layout>
    <x-slot name="header">
        Manajemen Pengguna
    </x-slot>

    {{-- Alert Messages --}}
    @if (session('success'))
        <div class="mb-5 rounded-input bg-success-bg border border-success/20 text-success px-4 py-3 text-body-sm flex items-center gap-2">
            <x-dynamic-component component="lucide-check-circle-2" class="w-4 h-4 shrink-0" />
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="mb-5 rounded-input bg-danger-bg border border-danger/20 text-danger px-4 py-3 text-body-sm flex items-center gap-2">
            <x-dynamic-component component="lucide-alert-circle" class="w-4 h-4 shrink-0" />
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Wrapper dengan State Alpine.js untuk Modal --}}
    <div x-data="{
        actionUrl: '',
        userName: '',
        isActive: false,
        openToggleModal(url, name, status) {
            this.actionUrl = url;
            this.userName = name;
            this.isActive = status;
            $dispatch('open-modal', 'confirm-toggle-user');
        }
    }">
        {{-- Users Table --}}
        <x-data-table
            title="Daftar Pengguna Sistem"
            subtitle="Kelola akses akun, penugasan role, dan status aktif staf"
            :headers="['Pengguna', 'Role / Peran', 'Status', 'Terdaftar', 'Aksi']"
            :empty="$users->isEmpty()"
        >
            <x-slot name="action">
                @can('create', \App\Models\User::class)
                    <x-button href="{{ route('identity.users.create') }}" variant="primary" size="sm" class="gap-1.5">
                        <x-dynamic-component component="lucide-user-plus" class="w-4 h-4" />
                        <span>Tambah Pengguna</span>
                    </x-button>
                @endcan
            </x-slot>

            <x-slot name="emptyState">
                <div class="flex flex-col items-center justify-center py-12 text-center">
                    <div class="w-12 h-12 rounded-full bg-primary-tint text-primary flex items-center justify-center mb-3">
                        <x-dynamic-component component="lucide-users" class="w-6 h-6" />
                    </div>
                    <h4 class="text-body-lg font-semibold text-ink-black">Belum Ada Pengguna</h4>
                    <p class="text-caption text-slate-gray mt-1 max-w-sm mb-4">Tambahkan pengguna pertama untuk mulai mengelola akses modul ERP.</p>
                    @can('create', \App\Models\User::class)
                        <x-button variant="primary" href="{{ route('identity.users.create') }}" size="sm">
                            + Tambah Pengguna
                        </x-button>
                    @endcan
                </div>
            </x-slot>

            @foreach ($users as $user)
                <tr class="hover:bg-mist-gray/40 transition-colors">
                    {{-- User Avatar & Details --}}
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-card bg-primary-tint text-primary font-bold flex items-center justify-center text-caption shrink-0 shadow-subtle">
                                {{ strtoupper(substr($user->name, 0, 2)) }}
                            </div>
                            <div>
                                <p class="text-body-sm font-semibold text-ink-black leading-tight">{{ $user->name }}</p>
                                <p class="text-caption text-slate-gray">{{ $user->email }}</p>
                            </div>
                        </div>
                    </td>

                    {{-- Roles --}}
                    <td class="px-6 py-4">
                        <div class="flex flex-wrap gap-1">
                            @forelse ($user->roles as $role)
                                <x-badge
                                    :status="match ($role->name) {
                                        'Super Admin'     => 'primary',
                                        'Finance Manager' => 'success',
                                        'Finance Staff'   => 'success',
                                        'Sales Staff'     => 'peach',
                                        'HR Manager'      => 'info',
                                        'HR Staff'        => 'info',
                                        default           => 'neutral'
                                    }"
                                    variant="subtle"
                                >
                                    {{ $role->name }}
                                </x-badge>
                            @empty
                                <span class="text-caption text-ash-gray italic">Tanpa Role</span>
                            @endforelse
                        </div>
                    </td>

                    {{-- Status --}}
                    <td class="px-6 py-4">
                        <x-badge :status="$user->is_active ? 'success' : 'neutral'" variant="solid">
                            {{ $user->is_active ? 'Online / Aktif' : 'Nonaktif' }}
                        </x-badge>
                    </td>

                    {{-- Registered Date --}}
                    <td class="px-6 py-4 text-caption font-medium text-slate-gray tabular-nums">
                        {{ $user->created_at ? $user->created_at->format('d/m/Y') : '-' }}
                    </td>

                    {{-- Actions --}}
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('identity.users.edit', $user) }}"
                               class="p-1.5 rounded-input text-slate-gray hover:text-primary hover:bg-mist-gray transition"
                               title="Edit Pengguna">
                                <x-dynamic-component component="lucide-pencil" class="w-4 h-4" />
                            </a>

                            @if (! $user->is(auth()->user()))
                                <button type="button"
                                        @click="openToggleModal('{{ route('identity.users.toggle-active', $user) }}', '{{ addslashes($user->name) }}', {{ $user->is_active ? 'true' : 'false' }})"
                                        class="p-1.5 rounded-input transition {{ $user->is_active ? 'text-danger hover:bg-danger-bg' : 'text-success hover:bg-success-bg' }}"
                                        title="{{ $user->is_active ? 'Nonaktifkan Pengguna' : 'Aktifkan Pengguna' }}">
                                    @if ($user->is_active)
                                        <x-dynamic-component component="lucide-user-x" class="w-4 h-4" />
                                    @else
                                        <x-dynamic-component component="lucide-user-check" class="w-4 h-4" />
                                    @endif
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach

            <x-slot name="pagination">
                {{ $users->links() }}
            </x-slot>
        </x-data-table>

        {{-- Modal Konfirmasi Status Pengguna --}}
        <x-modal name="confirm-toggle-user" maxWidth="md">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0"
                     :class="isActive ? 'bg-danger-bg text-danger' : 'bg-success-bg text-success'">
                    <template x-if="isActive">
                        <x-dynamic-component component="lucide-alert-triangle" class="w-5 h-5" />
                    </template>
                    <template x-if="!isActive">
                        <x-dynamic-component component="lucide-check-circle" class="w-5 h-5" />
                    </template>
                </div>
                <div>
                    <h3 class="text-heading-sm font-semibold text-ink-black"
                        x-text="isActive ? 'Nonaktifkan Akun Pengguna?' : 'Aktifkan Akun Pengguna?'"></h3>
                    <p class="text-caption text-slate-gray mt-0.5"
                       x-text="isActive ? 'Pengguna tidak akan dapat mengakses sistem' : 'Pengguna akan dapat login kembali ke sistem'"></p>
                </div>
            </div>

            <p class="text-body-sm text-slate-gray mb-6">
                Apakah Anda yakin ingin mengubah status akun untuk <strong class="text-ink-black" x-text="userName"></strong> menjadi <span class="font-semibold" :class="isActive ? 'text-danger' : 'text-success'" x-text="isActive ? 'Nonaktif' : 'Aktif'"></span>?
            </p>

            <form :action="actionUrl" method="POST" class="flex items-center justify-end gap-3 pt-4 border-t border-border-gray/60">
                @csrf
                @method('PATCH')

                <x-button variant="secondary" size="sm" type="button" x-on:click="$dispatch('close-modal', 'confirm-toggle-user')">
                    Batal
                </x-button>

                <button type="submit"
                        class="inline-flex items-center justify-center rounded-button font-medium transition-all duration-200 focus:outline-none px-4 py-2 text-body-sm text-paper-white"
                        :class="isActive ? 'bg-danger hover:opacity-90' : 'bg-success hover:opacity-90'">
                    <span x-text="isActive ? 'Ya, Nonaktifkan' : 'Ya, Aktifkan'"></span>
                </button>
            </form>
        </x-modal>
    </div>
</x-app-layout>
