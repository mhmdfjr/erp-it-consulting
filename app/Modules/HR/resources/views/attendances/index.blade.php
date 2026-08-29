<x-app-layout>
    <x-slot name="header">
        <span class="text-ink-black dark:text-paper-white">Kelola Kehadiran & Absensi (HRD)</span>
    </x-slot>

    {{-- Alert Messages --}}
    @if (session('success'))
        <div class="mb-5 rounded-input bg-success-bg dark:bg-success/10 border border-success/20 text-success px-4 py-3 text-body-sm flex items-center gap-2">
            <x-dynamic-component component="lucide-check-circle-2" class="w-4 h-4 shrink-0" />
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="mb-5 rounded-input bg-danger-bg dark:bg-danger/10 border border-danger/20 text-danger px-4 py-3 text-body-sm flex items-center gap-2">
            <x-dynamic-component component="lucide-alert-circle" class="w-4 h-4 shrink-0" />
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div class="space-y-6">
        {{-- Filter Bar Card --}}
        <div class="bg-paper-white dark:bg-[#111c44] border border-border-gray/80 dark:border-border-gray/10 rounded-card p-4 shadow-subtle flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <form method="GET" class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
                <div class="w-full sm:w-64">
                    <select
                        name="employee_id"
                        class="w-full rounded-input border border-border-gray dark:border-border-gray/20 bg-paper-white dark:bg-paper-white/5 px-3.5 py-2 text-body-sm text-ink-black dark:text-paper-white transition focus:ring-0 focus:outline-none focus:border-primary focus:shadow-focus-ring"
                        onchange="this.form.submit()"
                    >
                        <option value="" class="dark:bg-[#111c44] dark:text-paper-white">-- Semua Karyawan --</option>
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->id }}" @selected(request('employee_id') == $employee->id) class="dark:bg-[#111c44] dark:text-paper-white">
                                {{ $employee->full_name }} ({{ $employee->employee_code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="w-full sm:w-44">
                    <input
                        type="month"
                        name="month"
                        value="{{ request('month') }}"
                        class="w-full rounded-input border border-border-gray dark:border-border-gray/20 bg-paper-white dark:bg-paper-white/5 px-3.5 py-2 text-body-sm text-ink-black dark:text-paper-white transition focus:ring-0 focus:outline-none focus:border-primary focus:shadow-focus-ring"
                        onchange="this.form.submit()"
                    />
                </div>

                @if(request('employee_id') || request('month'))
                    <a href="{{ route('hr.attendances.index') }}" class="text-caption font-semibold text-danger hover:underline">
                        Reset Filter
                    </a>
                @endif
            </form>

            <div class="hidden lg:flex items-center gap-2 text-caption text-slate-gray dark:text-ash-gray">
                <x-dynamic-component component="lucide-calendar-days" class="w-4 h-4 text-primary" />
                <span>Total Data: <strong class="text-ink-black dark:text-paper-white tabular-nums">{{ $attendances->total() }} Log</strong></span>
            </div>
        </div>

        {{-- Attendances Table Card --}}
        <x-data-table
            title="Daftar Log Presensi & Kehadiran"
            subtitle="Rekapitulasi catatan jam masuk, jam pulang, dan status absensi harian staf"
            :headers="['Tanggal', 'Karyawan', 'Check In', 'Check Out', 'Status Absensi', 'Aksi']"
            :empty="$attendances->isEmpty()"
        >
            <x-slot name="action">
                @can('hr.attendance.manage')
                    <x-button variant="primary" size="sm" href="{{ route('hr.attendances.create') }}" class="gap-1.5">
                        <x-dynamic-component component="lucide-plus-circle" class="w-4 h-4" />
                        <span>Catat Kehadiran</span>
                    </x-button>
                @endcan
            </x-slot>

            <x-slot name="emptyState">
                <div class="flex flex-col items-center justify-center py-12 text-center">
                    <div class="w-12 h-12 rounded-full bg-primary/10 text-primary flex items-center justify-center mb-3">
                        <x-dynamic-component component="lucide-user-check" class="w-6 h-6" />
                    </div>
                    <h4 class="text-body-lg font-semibold text-ink-black dark:text-paper-white">Belum Ada Catatan Kehadiran</h4>
                    <p class="text-caption text-slate-gray dark:text-ash-gray mt-1 max-w-sm mb-4">Catat log presensi pertama untuk melacak rekap jam kerja karyawan.</p>
                    @can('hr.attendance.manage')
                        <x-button variant="primary" href="{{ route('hr.attendances.create') }}" size="sm">
                            + Catat Kehadiran
                        </x-button>
                    @endcan
                </div>
            </x-slot>

            @foreach ($attendances as $attendance)
                <tr class="hover:bg-mist-gray/40 dark:hover:bg-paper-white/5 transition-colors">
                    {{-- Tanggal --}}
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <x-dynamic-component component="lucide-calendar" class="w-3.5 h-3.5 text-ash-gray dark:text-slate-gray" />
                            <span class="text-body-sm font-medium text-ink-black dark:text-paper-white tabular-nums">
                                {{ $attendance->date ? $attendance->date->format('d M Y') : '-' }}
                            </span>
                        </div>
                    </td>

                    {{-- Karyawan --}}
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="h-8 w-8 rounded-card bg-primary/10 text-primary font-bold flex items-center justify-center text-caption shrink-0 shadow-subtle border border-primary/20">
                                {{ strtoupper(substr($attendance->employee?->full_name ?? '?', 0, 2)) }}
                            </div>
                            <div>
                                <p class="text-body-sm font-semibold text-ink-black dark:text-paper-white leading-tight">{{ $attendance->employee?->full_name ?? 'Karyawan Dihapus' }}</p>
                                <span class="font-mono text-[10px] text-slate-gray dark:text-ash-gray">{{ $attendance->employee?->employee_code ?? '-' }}</span>
                            </div>
                        </div>
                    </td>

                    {{-- Check In --}}
                    <td class="px-6 py-4 text-body-sm font-mono text-ink-black dark:text-paper-white tabular-nums">
                        {{ $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('H:i') : '-' }}
                    </td>

                    {{-- Check Out --}}
                    <td class="px-6 py-4 text-body-sm font-mono text-ink-black dark:text-paper-white tabular-nums">
                        {{ $attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('H:i') : '-' }}
                    </td>

                    {{-- Status Absensi --}}
                    <td class="px-6 py-4">
                        <x-badge
                            :status="match ($attendance->status) {
                                'present'       => 'success',
                                'absent'        => 'danger',
                                'leave', 'sick' => 'warning',
                                default         => 'neutral'
                            }"
                            variant="solid"
                        >
                            {{ ucfirst($attendance->status) }}
                        </x-badge>
                    </td>

                    {{-- Aksi --}}
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end">
                            @can('hr.attendance.manage')
                                <a href="{{ route('hr.attendances.edit', $attendance) }}"
                                   class="p-1.5 rounded-input text-slate-gray dark:text-ash-gray hover:text-primary dark:hover:text-primary hover:bg-mist-gray dark:hover:bg-paper-white/10 transition"
                                   title="Edit Kehadiran">
                                    <x-dynamic-component component="lucide-pencil" class="w-4 h-4" />
                                </a>
                            @endcan
                        </div>
                    </td>
                </tr>
            @endforeach

            <x-slot name="pagination">
                {{ $attendances->links() }}
            </x-slot>
        </x-data-table>
    </div>
</x-app-layout>
