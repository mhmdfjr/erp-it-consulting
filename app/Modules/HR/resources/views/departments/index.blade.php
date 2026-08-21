<x-app-layout>
    <x-slot name="header">
        Manajemen Departemen (HRD)
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

    {{-- Departments Table Card --}}
    <x-data-table
        title="Daftar Departemen Organisasi"
        subtitle="Kelola struktur unit kerja divisi dan alokasi jabatan dalam hierarki perusahaan"
        :headers="['Nama Departemen / Divisi', 'Total Posisi Jabatan', 'Aksi']"
        :empty="$departments->isEmpty()"
    >
        <x-slot name="action">
            @can('hr.department.manage')
                <x-button variant="primary" size="sm" href="{{ route('hr.departments.create') }}" class="gap-1.5">
                    <x-dynamic-component component="lucide-plus-circle" class="w-4 h-4" />
                    <span>Tambah Departemen</span>
                </x-button>
            @endcan
        </x-slot>

        <x-slot name="emptyState">
            <div class="flex flex-col items-center justify-center py-12 text-center">
                <div class="w-12 h-12 rounded-full bg-primary-tint text-primary flex items-center justify-center mb-3">
                    <x-dynamic-component component="lucide-building-2" class="w-6 h-6" />
                </div>
                <h4 class="text-body-lg font-semibold text-ink-black">Belum Ada Departemen</h4>
                <p class="text-caption text-slate-gray mt-1 max-w-sm mb-4">Tambahkan unit divisi kerja pertama sebelum menetapkan posisi jabatan dan karyawan.</p>
                @can('hr.department.manage')
                    <x-button variant="primary" href="{{ route('hr.departments.create') }}" size="sm">
                        + Tambah Departemen
                    </x-button>
                @endcan
            </div>
        </x-slot>

        @foreach ($departments as $department)
            <tr class="hover:bg-mist-gray/40 transition-colors">
                {{-- Nama Departemen & Icon --}}
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-card bg-primary-tint text-primary flex items-center justify-center shrink-0 shadow-subtle">
                            <x-dynamic-component component="lucide-network" class="w-5 h-5" />
                        </div>
                        <div>
                            <p class="text-body-sm font-semibold text-ink-black leading-tight">{{ $department->name }}</p>
                            <span class="text-[11px] text-slate-gray">Unit Divisi Perusahaan</span>
                        </div>
                    </div>
                </td>

                {{-- Jumlah Posisi Jabatan --}}
                <td class="px-6 py-4">
                    <span class="inline-flex items-center gap-1.5 text-caption font-semibold text-slate-gray tabular-nums bg-fog-white border border-border-gray/80 px-2.5 py-1 rounded-input">
                        <x-dynamic-component component="lucide-briefcase" class="w-3.5 h-3.5 text-ash-gray" />
                        {{ $department->positions_count ?? 0 }} Jabatan Terdaftar
                    </span>
                </td>

                {{-- Aksi --}}
                <td class="px-6 py-4 text-right">
                    <div class="flex items-center justify-end">
                        @can('hr.department.manage')
                            <a href="{{ route('hr.departments.edit', $department) }}"
                               class="p-1.5 rounded-input text-slate-gray hover:text-primary hover:bg-mist-gray transition"
                               title="Edit Departemen">
                                <x-dynamic-component component="lucide-pencil" class="w-4 h-4" />
                            </a>
                        @endcan
                    </div>
                </td>
            </tr>
        @endforeach

        <x-slot name="pagination">
            {{ $departments->links() }}
        </x-slot>
    </x-data-table>
</x-app-layout>
