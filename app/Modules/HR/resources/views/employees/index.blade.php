<x-app-layout>
    <x-slot name="header">
        Manajemen Data Karyawan (HRD)
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

    {{-- Employees Table Card --}}
    <x-data-table
        title="Daftar Karyawan Aktif & Staf"
        subtitle="Kelola data induk kepegawaian, penempatan divisi, PTKP pajak, dan integrasi penggajian"
        :headers="['Karyawan & NIK', 'Posisi & Departemen', 'Status PTKP', 'Status Kerja', 'Aksi']"
        :empty="$employees->isEmpty()"
    >
        <x-slot name="action">
            @can('hr.employee.create')
                <x-button variant="primary" size="sm" href="{{ route('hr.employees.create') }}" class="gap-1.5">
                    <x-dynamic-component component="lucide-user-plus" class="w-4 h-4" />
                    <span>Tambah Karyawan</span>
                </x-button>
            @endcan
        </x-slot>

        <x-slot name="emptyState">
            <div class="flex flex-col items-center justify-center py-12 text-center">
                <div class="w-12 h-12 rounded-full bg-primary-tint text-primary flex items-center justify-center mb-3">
                    <x-dynamic-component component="lucide-users" class="w-6 h-6" />
                </div>
                <h4 class="text-body-lg font-semibold text-ink-black">Belum Ada Data Karyawan</h4>
                <p class="text-caption text-slate-gray mt-1 max-w-sm mb-4">Tambahkan profil karyawan pertama untuk mulai mengelola struktur organisasi dan slip gaji (payroll).</p>
                @can('hr.employee.create')
                    <x-button variant="primary" href="{{ route('hr.employees.create') }}" size="sm">
                        + Tambah Karyawan
                    </x-button>
                @endcan
            </div>
        </x-slot>

        @foreach ($employees as $employee)
            <tr class="hover:bg-mist-gray/40 transition-colors">
                {{-- Avatar & Nama Karyawan --}}
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-card bg-primary-tint text-primary font-bold flex items-center justify-center text-caption shrink-0 shadow-subtle">
                            {{ strtoupper(substr($employee->full_name, 0, 2)) }}
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <p class="text-body-sm font-semibold text-ink-black leading-tight">{{ $employee->full_name }}</p>
                                <span class="font-mono text-[10px] font-bold text-primary bg-primary-tint/60 px-1.5 py-0.5 rounded-input border border-primary/20">
                                    {{ $employee->employee_code }}
                                </span>
                            </div>
                            <p class="text-caption text-slate-gray mt-0.5">{{ $employee->email ?: ($employee->phone ?: 'NIK: ' . ($employee->nik ?: '-')) }}</p>
                        </div>
                    </div>
                </td>

                {{-- Posisi & Departemen --}}
                <td class="px-6 py-4">
                    <div>
                        <p class="text-body-sm font-semibold text-ink-black leading-tight">{{ $employee->position?->title ?? '-' }}</p>
                        <p class="text-caption text-slate-gray font-medium mt-0.5">{{ $employee->position?->department?->name ?? 'Tanpa Departemen' }}</p>
                    </div>
                </td>

                {{-- Status PTKP --}}
                <td class="px-6 py-4">
                    <span class="font-mono text-caption font-semibold text-slate-gray bg-fog-white border border-border-gray/80 px-2.5 py-1 rounded-input tabular-nums">
                        {{ $employee->ptkp_status ?? 'TK0' }}
                    </span>
                </td>

                {{-- Status Kepegawaian --}}
                <td class="px-6 py-4">
                    <x-badge
                        :status="match ($employee->employment_status) {
                            'active'     => 'success',
                            'resigned'   => 'warning',
                            'terminated' => 'danger',
                            default      => 'neutral'
                        }"
                        variant="solid"
                    >
                        {{ ucfirst($employee->employment_status) }}
                    </x-badge>
                </td>

                {{-- Aksi --}}
                <td class="px-6 py-4 text-right">
                    <div class="flex items-center justify-end gap-1.5">
                        <a href="{{ route('hr.employees.payroll-components.index', $employee) }}"
                           class="p-1.5 rounded-input text-slate-gray hover:text-primary hover:bg-mist-gray transition"
                           title="Kelola Komponen Gaji">
                            <x-dynamic-component component="lucide-banknote" class="w-4 h-4" />
                        </a>

                        @can('hr.employee.update')
                            <a href="{{ route('hr.employees.edit', $employee) }}"
                               class="p-1.5 rounded-input text-slate-gray hover:text-primary hover:bg-mist-gray transition"
                               title="Edit Data Karyawan">
                                <x-dynamic-component component="lucide-pencil" class="w-4 h-4" />
                            </a>
                        @endcan
                    </div>
                </td>
            </tr>
        @endforeach

        <x-slot name="pagination">
            {{ $employees->links() }}
        </x-slot>
    </x-data-table>
</x-app-layout>
