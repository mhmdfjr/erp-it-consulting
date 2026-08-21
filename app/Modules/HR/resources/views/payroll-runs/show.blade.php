<x-app-layout>
    <x-slot name="header">
        Proses Gaji Periode: {{ \Carbon\Carbon::create($period->period_year, $period->period_month, 1)->translatedFormat('F Y') }}
    </x-slot>

    {{-- Alert Notifications --}}
    @if (session('success'))
        <div class="mb-5 rounded-input bg-success-bg border border-success/20 text-success px-4 py-3 text-body-sm flex items-center gap-2">
            <x-dynamic-component component="lucide-check-circle-2" class="w-4 h-4 shrink-0" />
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if (session('warning'))
        <div class="mb-5 rounded-input bg-warning-bg border border-warning/30 text-ink-black px-4 py-3 text-body-sm flex items-center gap-2">
            <x-dynamic-component component="lucide-alert-triangle" class="w-4 h-4 text-warning shrink-0" />
            <span>{{ session('warning') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="mb-5 rounded-input bg-danger-bg border border-danger/20 text-danger px-4 py-3 text-body-sm flex items-center gap-2">
            <x-dynamic-component component="lucide-alert-circle" class="w-4 h-4 shrink-0" />
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Failed Employees Alert --}}
    @if (session('failedEmployees') && count(session('failedEmployees')) > 0)
        <div class="mb-5 rounded-card bg-danger-bg border border-danger/30 text-danger p-4 shadow-subtle">
            <div class="flex items-center gap-2 font-semibold text-body-sm mb-2">
                <x-dynamic-component component="lucide-alert-octagon" class="w-4 h-4 shrink-0" />
                <span>Karyawan yang Gagal Diproses:</span>
            </div>
            <ul class="text-caption list-disc list-inside space-y-1">
                @foreach (session('failedEmployees') as $failure)
                    <li>Employee #{{ $failure['employee_id'] }}: {{ $failure['error'] }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="space-y-6">
        {{-- Summary Header Card --}}
        <div class="bg-paper-white border border-border-gray/80 rounded-card p-6 shadow-subtle flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="h-12 w-12 rounded-card bg-primary-tint text-primary flex items-center justify-center shrink-0 shadow-subtle">
                    <x-dynamic-component component="lucide-banknote" class="w-6 h-6" />
                </div>
                <div>
                    <div class="flex items-center gap-3">
                        <h2 class="text-heading-sm font-bold text-ink-black tracking-tight">
                            Gaji Periode {{ \Carbon\Carbon::create($period->period_year, $period->period_month, 1)->translatedFormat('F Y') }}
                        </h2>
                        <x-badge
                            :status="match ($period->status) {
                                'paid'      => 'success',
                                'processed' => 'warning',
                                default     => 'info'
                            }"
                            variant="solid"
                        >
                            {{ ucfirst($period->status) }}
                        </x-badge>
                    </div>
                    <p class="text-caption text-slate-gray mt-1 font-medium">
                        Total Karyawan: <strong class="text-ink-black tabular-nums">{{ $runs->count() }} Orang</strong>
                    </p>
                </div>
            </div>

            {{-- Action Controls --}}
            <div class="flex flex-wrap items-center gap-2.5 self-end md:self-auto">
                <x-button variant="secondary" size="sm" href="{{ route('hr.payroll-runs.index') }}" class="gap-1.5">
                    <x-dynamic-component component="lucide-arrow-left" class="w-4 h-4" />
                    <span>Kembali</span>
                </x-button>

                @if ($period->status === 'draft')
                    @can('hr.payroll.process')
                        @if (empty($incompleteAttendance) || count($incompleteAttendance) === 0)
                            <form method="POST" action="{{ route('hr.payroll-runs.process', $period) }}">
                                @csrf
                                <x-button type="submit" variant="primary" size="sm" class="gap-1.5">
                                    <x-dynamic-component component="lucide-play" class="w-4 h-4" />
                                    <span>Process Payroll</span>
                                </x-button>
                            </form>
                        @endif
                    @endcan
                @endif

                @if ($period->status === 'processed')
                    @can('hr.payroll.pay')
                        <form method="POST" action="{{ route('hr.payroll-runs.mark-as-paid', $period) }}">
                            @csrf
                            <x-button type="submit" variant="primary" size="sm" class="gap-1.5">
                                <x-dynamic-component component="lucide-check-circle" class="w-4 h-4" />
                                <span>Tandai Semua Lunas (Paid)</span>
                            </x-button>
                        </form>
                    @endcan
                @endif
            </div>
        </div>

        {{-- Incomplete Attendance Warning Card for Draft Periods --}}
        @if ($period->status === 'draft' && !empty($incompleteAttendance) && count($incompleteAttendance) > 0)
            @can('hr.payroll.process')
                <div class="rounded-card bg-warning-bg border border-warning/30 p-5 shadow-subtle">
                    <div class="flex items-start gap-3">
                        <x-dynamic-component component="lucide-alert-triangle" class="w-5 h-5 text-warning shrink-0 mt-0.5" />
                        <div class="flex-1">
                            <h4 class="font-bold text-body-sm text-ink-black">
                                Data Kehadiran Belum Lengkap untuk {{ count($incompleteAttendance) }} Karyawan
                            </h4>
                            <p class="text-caption text-slate-gray mt-0.5">
                                Hari kerja tanpa log presensi akan secara otomatis dianggap <strong>"Present" (Hadir)</strong> apabila proses dipaksa berlanjut.
                            </p>

                            <div class="mt-3 bg-paper-white/80 border border-warning/20 rounded-input p-3 max-h-40 overflow-y-auto">
                                <ul class="text-caption text-ink-black space-y-1">
                                    @foreach ($incompleteAttendance as $item)
                                        <li class="flex items-center justify-between">
                                            <span>{{ $item['employee_name'] }}</span>
                                            <span class="font-mono font-semibold tabular-nums text-slate-gray">{{ $item['actual'] }}/{{ $item['expected'] }} hari</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>

                            <div class="flex flex-wrap items-center gap-3 mt-4 pt-3 border-t border-warning/20">
                                <form method="POST" action="{{ route('hr.payroll-runs.process', $period) }}">
                                    @csrf
                                    <input type="hidden" name="force" value="1" />
                                    <x-button type="submit" variant="danger" size="sm" class="gap-1.5">
                                        <x-dynamic-component component="lucide-zap" class="w-4 h-4" />
                                        <span>Proses Tetap Lanjut (Force)</span>
                                    </x-button>
                                </form>

                                <form method="POST" action="{{ route('hr.payroll-runs.cancel', $period) }}" onsubmit="return confirm('Batalkan draft periode payroll ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <x-button type="submit" variant="secondary" size="sm">
                                        Batalkan Draft Periode
                                    </x-button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endcan
        @endif

        {{-- Runs List Table Card --}}
        <x-data-table
            title="Daftar Gaji Karyawan"
            subtitle="Rincian perolehan gaji kotor, kehadiran, potongan pajak PPh 21, dan gaji bersih (take home pay)"
            :headers="['Karyawan', 'Hari Kerja', 'Alfa', 'Gaji Pokok', 'Gaji Kotor', 'PPh 21 (TER)', 'Gaji Bersih (Net)', 'Status', 'Aksi']"
            :empty="$runs->isEmpty()"
        >
            <x-slot name="emptyState">
                <div class="flex flex-col items-center justify-center py-10 text-center">
                    <x-dynamic-component component="lucide-wallet" class="w-8 h-8 text-ash-gray mb-2" />
                    <p class="text-body font-medium text-ink-black">Belum Ada Gaji yang Diproses</p>
                    <p class="text-caption text-slate-gray mt-0.5">Klik tombol 'Process Payroll' di atas untuk menjalankan kalkulasi.</p>
                </div>
            </x-slot>

            @foreach ($runs as $run)
                <tr class="hover:bg-mist-gray/40 transition-colors">
                    {{-- Nama Karyawan --}}
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="h-8 w-8 rounded-card bg-primary-tint text-primary font-bold flex items-center justify-center text-caption shrink-0 shadow-subtle">
                                {{ strtoupper(substr($run->employee?->full_name ?? '?', 0, 2)) }}
                            </div>
                            <div>
                                <p class="text-body-sm font-semibold text-ink-black leading-tight">{{ $run->employee?->full_name ?? '-' }}</p>
                                <span class="font-mono text-[10px] text-slate-gray">{{ $run->employee?->employee_code ?? '-' }}</span>
                            </div>
                        </div>
                    </td>

                    {{-- Hari Kerja --}}
                    <td class="px-6 py-4 text-body-sm text-center tabular-nums font-medium text-ink-black">
                        {{ $run->working_days }}
                    </td>

                    {{-- Absent --}}
                    <td class="px-6 py-4 text-body-sm text-center tabular-nums font-semibold {{ $run->absent_days > 0 ? 'text-danger' : 'text-slate-gray' }}">
                        {{ $run->absent_days }}
                    </td>

                    {{-- Base Salary --}}
                    <td class="px-6 py-4 text-body-sm text-right tabular-nums text-slate-gray font-medium">
                        Rp {{ number_format($run->base_salary, 2, ',', '.') }}
                    </td>

                    {{-- Gross --}}
                    <td class="px-6 py-4 text-body-sm text-right tabular-nums text-ink-black font-semibold">
                        Rp {{ number_format($run->gross_salary, 2, ',', '.') }}
                    </td>

                    {{-- PPh21 --}}
                    <td class="px-6 py-4 text-body-sm text-right tabular-nums text-danger font-semibold">
                        Rp {{ number_format($run->pph21_deduction, 2, ',', '.') }}
                    </td>

                    {{-- Net Salary --}}
                    <td class="px-6 py-4 text-body-sm text-right tabular-nums text-primary font-bold">
                        Rp {{ number_format($run->net_salary, 2, ',', '.') }}
                    </td>

                    {{-- Status --}}
                    <td class="px-6 py-4">
                        <x-badge
                            :status="$run->status === 'paid' ? 'success' : 'info'"
                            variant="subtle"
                        >
                            {{ ucfirst($run->status) }}
                        </x-badge>
                    </td>

                    {{-- Aksi --}}
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('hr.payroll-runs.slip', $run) }}"
                           class="p-1.5 rounded-input text-slate-gray hover:text-primary hover:bg-mist-gray transition inline-flex items-center"
                           title="Lihat Slip Gaji">
                            <x-dynamic-component component="lucide-receipt-text" class="w-4 h-4" />
                        </a>
                    </td>
                </tr>
            @endforeach
        </x-data-table>
    </div>
</x-app-layout>
