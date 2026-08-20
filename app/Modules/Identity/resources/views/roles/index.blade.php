<x-app-layout>
    <x-slot name="header">
        Manajemen Peran & Perizinan
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

    <div x-data="{
        deleteUrl: '',
        roleName: '',
        openDeleteModal(url, name) {
            this.deleteUrl = url;
            this.roleName = name;
            $dispatch('open-modal', 'confirm-delete-role');
        }
    }">
        {{-- Roles Table Card --}}
        <x-data-table
            title="Daftar Peran & Hak Akses"
            subtitle="Atur batasan otorisasi modul dan privilege pengguna di sistem"
            :headers="['Nama Role', 'Akses Modul / Permissions', 'Total Izin', 'Aksi']"
            :empty="$roles->isEmpty()"
        >
            <x-slot name="action">
                @can('create', \Spatie\Permission\Models\Role::class)
                    <x-button href="{{ route('identity.roles.create') }}" variant="primary" size="sm" class="gap-1.5">
                        <x-dynamic-component component="lucide-shield-plus" class="w-4 h-4" />
                        <span>Tambah Peran</span>
                    </x-button>
                @endcan
            </x-slot>

            <x-slot name="emptyState">
                <div class="flex flex-col items-center justify-center py-12 text-center">
                    <div class="w-12 h-12 rounded-full bg-primary-tint text-primary flex items-center justify-center mb-3">
                        <x-dynamic-component component="lucide-shield-alert" class="w-6 h-6" />
                    </div>
                    <h4 class="text-body-lg font-semibold text-ink-black">Belum Ada Peran</h4>
                    <p class="text-caption text-slate-gray mt-1 max-w-sm mb-4">Tambahkan peran pertama untuk mendistribusikan hak akses pengguna.</p>
                    @can('create', \Spatie\Permission\Models\Role::class)
                        <x-button variant="primary" href="{{ route('identity.roles.create') }}" size="sm">
                            + Tambah Peran
                        </x-button>
                    @endcan
                </div>
            </x-slot>

            @foreach ($roles as $role)
                <tr class="hover:bg-mist-gray/40 transition-colors">
                    {{-- Role Name & Badge Indicator --}}
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-card bg-primary-tint text-primary flex items-center justify-center shrink-0 shadow-subtle">
                                <x-dynamic-component component="lucide-shield" class="w-5 h-5" />
                            </div>
                            <div>
                                <p class="text-body-sm font-semibold text-ink-black leading-tight">{{ $role->name }}</p>
                                <span class="text-[11px] text-slate-gray">Guard: {{ $role->guard_name }}</span>
                            </div>
                        </div>
                    </td>

                    {{-- Role Type Category --}}
                    <td class="px-6 py-4">
                        <x-badge
                            :status="match ($role->name) {
                                'Super Admin'     => 'primary',
                                'Finance Manager', 'Finance Staff' => 'success',
                                'Sales Staff'     => 'peach',
                                'HR Manager', 'HR Staff' => 'info',
                                default           => 'neutral'
                            }"
                            variant="subtle"
                        >
                            {{ $role->name === 'Super Admin' ? 'All Permissions Granted' : 'Restricted Role' }}
                        </x-badge>
                    </td>

                    {{-- Permissions Count --}}
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center gap-1.5 text-caption font-semibold text-ink-black tabular-nums bg-fog-white border border-border-gray/80 px-2.5 py-1 rounded-input">
                            <x-dynamic-component component="lucide-key" class="w-3.5 h-3.5 text-slate-gray" />
                            {{ $role->permissions_count }} Izin
                        </span>
                    </td>

                    {{-- Actions --}}
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('identity.roles.edit', $role) }}"
                               class="p-1.5 rounded-input text-slate-gray hover:text-primary hover:bg-mist-gray transition"
                               title="Edit Peran & Izin">
                                <x-dynamic-component component="lucide-pencil" class="w-4 h-4" />
                            </a>

                            @if ($role->name !== 'Super Admin')
                                <button type="button"
                                        @click="openDeleteModal('{{ route('identity.roles.destroy', $role) }}', '{{ addslashes($role->name) }}')"
                                        class="p-1.5 rounded-input text-danger hover:bg-danger-bg transition"
                                        title="Hapus Role">
                                    <x-dynamic-component component="lucide-trash-2" class="w-4 h-4" />
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach

            <x-slot name="pagination">
                {{ $roles->links() }}
            </x-slot>
        </x-data-table>

        {{-- Modal Konfirmasi Hapus Role --}}
        <x-modal name="confirm-delete-role" maxWidth="md">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-danger-bg text-danger flex items-center justify-center shrink-0">
                    <x-dynamic-component component="lucide-alert-triangle" class="w-5 h-5" />
                </div>
                <div>
                    <h3 class="text-heading-sm font-semibold text-ink-black">Hapus Peran & Izin?</h3>
                    <p class="text-caption text-slate-gray mt-0.5">Tindakan ini tidak dapat dibatalkan</p>
                </div>
            </div>

            <p class="text-body-sm text-slate-gray mb-6">
                Apakah Anda yakin ingin menghapus peran <strong class="text-ink-black" x-text="roleName"></strong>? Semua user dengan peran ini akan kehilangan hak akses yang terhubung.
            </p>

            <form :action="deleteUrl" method="POST" class="flex items-center justify-end gap-3 pt-4 border-t border-border-gray/60">
                @csrf
                @method('DELETE')

                <x-button variant="secondary" size="sm" type="button" x-on:click="$dispatch('close-modal', 'confirm-delete-role')">
                    Batal
                </x-button>

                <x-button variant="danger" size="sm" type="submit" class="bg-danger text-paper-white hover:opacity-90">
                    Ya, Hapus Peran
                </x-button>
            </form>
        </x-modal>
    </div>
</x-app-layout>
