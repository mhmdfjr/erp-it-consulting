<x-app-layout>
    <x-slot name="header">
        Manajemen Jabatan (Positions)
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

    {{-- Positions Table Card --}}
    <x-data-table
        title="Daftar Jabatan Organisasi"
        subtitle="Kelola posisi jabatan struktural dan penempatan divisi karyawan"
        :headers="['Posisi Jabatan', 'Departemen / Divisi', 'Aksi']"
        :empty="$positions->isEmpty()"
    >
        <x-slot name="action">
            @can('hr.position.manage')
                <x-button variant="primary" size="sm" href="{{ route('hr.positions.create') }}" class="gap-1.5">
                    <x-dynamic-component component="lucide-plus-circle" class="w-4 h-4" />
                    <span>Tambah Jabatan</span>
                </x-button>
            @endcan
        </x-slot>

        <x-slot name="emptyState">
            <div class="flex flex-col items-center justify-center py-12 text-center">
                <div class="w-12 h-12 rounded-full bg-primary-tint text-primary flex items-center justify-center mb-3">
                    <x-dynamic-component component="lucide-briefcase" class="w-6 h-6" />
                </div>
                <h4 class="text-body-lg font-semibold text-ink-black">Belum Ada Jabatan</h4>
                <p class="text-caption text-slate-gray mt-1 max-w-sm mb-4">Tambahkan posisi jabatan pertama untuk departemen yang telah terdaftar.</p>
                @can('hr.position.manage')
                    <x-button variant="primary" href="{{ route('hr.positions.create') }}" size="sm">
                        + Tambah Jabatan
                    </x-button>
                @endcan
            </div>
        </x-slot>

        @foreach ($positions as $position)
            <tr class="hover:bg-mist-gray/40 transition-colors">
                {{-- Nama Posisi Jabatan & Icon --}}
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-card bg-primary-tint text-primary flex items-center justify-center shrink-0 shadow-subtle">
                            <x-dynamic-component component="lucide-award" class="w-5 h-5" />
                        </div>
                        <div>
                            <p class="text-body-sm font-semibold text-ink-black leading-tight">{{ $position->title }}</p>
                            <span class="text-[11px] text-slate-gray">Jabatan Profesional</span>
                        </div>
                    </div>
                </td>

                {{-- Departemen Terkait --}}
                <td class="px-6 py-4">
                    <div class="flex items-center gap-2">
                        <x-badge status="primary" variant="subtle" class="gap-1.5">
                            <x-dynamic-component component="lucide-building-2" class="w-3.5 h-3.5 shrink-0" />
                            <span>{{ $position->department?->name ?? 'Tanpa Departemen' }}</span>
                        </x-badge>
                    </div>
                </td>

                {{-- Aksi --}}
                <td class="px-6 py-4 text-right">
                    <div class="flex items-center justify-end">
                        @can('hr.position.manage')
                            <a href="{{ route('hr.positions.edit', $position) }}"
                               class="p-1.5 rounded-input text-slate-gray hover:text-primary hover:bg-mist-gray transition"
                               title="Edit Jabatan">
                                <x-dynamic-component component="lucide-pencil" class="w-4 h-4" />
                            </a>
                        @endcan
                    </div>
                </td>
            </tr>
        @endforeach

        <x-slot name="pagination">
            {{ $positions->links() }}
        </x-slot>
    </x-data-table>
</x-app-layout>
