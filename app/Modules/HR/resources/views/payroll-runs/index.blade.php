<x-app-layout>
    <x-slot name="header">
        Proses Gaji (Payroll Runs)
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

    {{-- Payroll Runs Table Card --}}
    <x-data-table
        title="Daftar Periode Penggajian"
        subtitle="Kelola siklus perhitungan gaji bulanan karyawan, kalkulasi PPh 21 TER, dan status pencairan"
        :headers="['Periode Penggajian', 'Karyawan Diproses', 'Status Pembayaran', 'Aksi']"
        :empty="$periods->isEmpty()"
    >
        <x-slot name="action">
            @can('hr.payroll.process')
                <x-button variant="primary" size="sm" href="{{ route('hr.payroll-runs.create') }}" class="gap-1.5">
                    <x-dynamic-component component="lucide-plus-circle" class="w-4 h-4" />
                    <span>Buat Periode Gaji</span>
                </x-button>
            @endcan
        </x-slot>

        <x-slot name="emptyState">
            <div class="flex flex-col items-center justify-center py-12 text-center">
                <div class="w-12 h-12 rounded-full bg-primary-tint text-primary flex items-center justify-center mb-3">
                    <x-dynamic-component component="lucide-banknote" class="w-6 h-6" />
                </div>
                <h4 class="text-body-lg font-semibold text-ink-black">Belum Ada Periode Gaji</h4>
                <p class="text-caption text-slate-gray mt-1 max-w-sm mb-4">Buat periode penggajian baru untuk memproses kalkulasi gaji dan potongan pajak PPh 21.</p>
                @can('hr.payroll.process')
                    <x-button variant="primary" href="{{ route('hr.payroll-runs.create') }}" size="sm">
                        + Buat Periode Gaji
                    </x-button>
                @endcan
            </div>
        </x-slot>

        @foreach ($periods as $period)
            <tr class="hover:bg-mist-gray/40 transition-colors">
                {{-- Periode Penggajian --}}
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-card bg-primary-tint text-primary font-bold flex items-center justify-center shrink-0 shadow-subtle">
                            <x-dynamic-component component="lucide-calendar" class="w-5 h-5" />
                        </div>
                        <div>
                            <p class="text-body-sm font-semibold text-ink-black leading-tight">
                                {{ \Carbon\Carbon::create($period->period_year, $period->period_month, 1)->translatedFormat('F Y') }}
                            </p>
                            <span class="text-caption text-slate-gray">
                                Siklus Bulanan ({{ $period->period_month }}/{{ $period->period_year }})
                            </span>
                        </div>
                    </div>
                </td>

                {{-- Jumlah Karyawan Diproses --}}
                <td class="px-6 py-4">
                    <span class="inline-flex items-center gap-1.5 text-caption font-semibold text-slate-gray tabular-nums bg-fog-white border border-border-gray/80 px-2.5 py-1 rounded-input">
                        <x-dynamic-component component="lucide-users" class="w-3.5 h-3.5 text-ash-gray" />
                        {{ $period->payroll_runs_count }} Karyawan
                    </span>
                </td>

                {{-- Status --}}
                <td class="px-6 py-4">
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
                </td>

                {{-- Aksi --}}
                <td class="px-6 py-4 text-right">
                    <div class="flex items-center justify-end">
                        <a href="{{ route('hr.payroll-runs.show', $period) }}"
                           class="p-1.5 rounded-input text-slate-gray hover:text-primary hover:bg-mist-gray transition"
                           title="Lihat Detail Penggajian">
                            <x-dynamic-component component="lucide-eye" class="w-4 h-4" />
                        </a>
                    </div>
                </td>
            </tr>
        @endforeach

        <x-slot name="pagination">
            {{ $periods->links() }}
        </x-slot>
    </x-data-table>
</x-app-layout>
