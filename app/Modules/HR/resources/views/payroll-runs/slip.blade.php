<x-app-layout>
    <x-slot name="header">
        Slip Gaji: {{ $run->employee?->full_name }}
    </x-slot>

    @php
        $periodDate = \Carbon\Carbon::create($run->payrollPeriod->period_year, $run->payrollPeriod->period_month, 1);
    @endphp

    <div class="max-w-3xl mx-auto space-y-6">
        {{-- Action Bar --}}
        <div class="flex items-center justify-between">
            <x-button variant="secondary" size="sm" href="{{ route('hr.payroll-runs.show', $run->payroll_period_id) }}" class="gap-1.5">
                <x-dynamic-component component="lucide-arrow-left" class="w-4 h-4" />
                <span>Kembali ke Payroll Run</span>
            </x-button>

            <x-button variant="secondary" size="sm" onclick="window.print()" class="gap-1.5">
                <x-dynamic-component component="lucide-printer" class="w-4 h-4" />
                <span>Cetak Slip</span>
            </x-button>
        </div>

        {{-- Official Payslip Paper Card --}}
        <div class="bg-paper-white border border-border-gray/80 rounded-card shadow-subtle overflow-hidden">
            {{-- Payslip Header --}}
            <div class="px-6 py-5 border-b border-border-gray/60 bg-fog-white/60 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div class="h-12 w-12 rounded-card bg-primary-tint text-primary flex items-center justify-center shrink-0 shadow-subtle">
                        <x-dynamic-component component="lucide-receipt" class="w-6 h-6" />
                    </div>
                    <div>
                        <h2 class="text-heading-sm font-bold text-ink-black tracking-tight">SLIP GAJI KARYAWAN</h2>
                        <p class="text-caption font-semibold text-primary mt-0.5">
                            Periode: {{ $periodDate->translatedFormat('F Y') }}
                        </p>
                    </div>
                </div>

                <x-badge
                    :status="$run->status === 'paid' ? 'success' : 'info'"
                    variant="solid"
                >
                    Status: {{ ucfirst($run->status) }}
                </x-badge>
            </div>

            {{-- Employee Identification Grid --}}
            <div class="p-6 border-b border-border-gray/60 bg-paper-white">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-body-sm">
                    <div class="space-y-2">
                        <div class="flex justify-between border-b border-border-gray/40 pb-1.5">
                            <span class="text-slate-gray">Nama Karyawan</span>
                            <strong class="text-ink-black">{{ $run->employee?->full_name }}</strong>
                        </div>
                        <div class="flex justify-between border-b border-border-gray/40 pb-1.5">
                            <span class="text-slate-gray">Nomor NIK / Code</span>
                            <span class="font-mono font-semibold text-ink-black">{{ $run->employee?->employee_code }}</span>
                        </div>
                        <div class="flex justify-between border-b border-border-gray/40 pb-1.5">
                            <span class="text-slate-gray">Status PTKP</span>
                            <span class="font-mono font-semibold text-ink-black">{{ $run->employee?->ptkp_status ?? 'TK0' }}</span>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <div class="flex justify-between border-b border-border-gray/40 pb-1.5">
                            <span class="text-slate-gray">Posisi Jabatan</span>
                            <strong class="text-ink-black">{{ $run->employee?->position?->title ?? '-' }}</strong>
                        </div>
                        <div class="flex justify-between border-b border-border-gray/40 pb-1.5">
                            <span class="text-slate-gray">Departemen</span>
                            <span class="font-medium text-ink-black">{{ $run->employee?->position?->department?->name ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between border-b border-border-gray/40 pb-1.5">
                            <span class="text-slate-gray">Kehadiran Efektif</span>
                            <span class="font-medium text-ink-black tabular-nums">
                                {{ $run->working_days - $run->absent_days }} dari {{ $run->working_days }} Hari Kerja
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Prorate Attendance Notice --}}
            @if ($run->absent_days > 0)
                <div class="m-6 mb-0 rounded-input bg-warning-bg border border-warning/30 p-3.5 flex items-start gap-2.5">
                    <x-dynamic-component component="lucide-info" class="w-4 h-4 text-warning shrink-0 mt-0.5" />
                    <p class="text-[12px] text-ink-black leading-relaxed">
                        Gaji pokok di-prorate karena terdapat <strong>{{ $run->absent_days }} hari ketidakhadiran</strong> (Alfa) dari total {{ $run->working_days }} hari kerja periode ini. Tunjangan tetap dibayarkan penuh.
                    </p>
                </div>
            @endif

            {{-- Earnings & Deductions Tables --}}
            <div class="p-6 space-y-6">
                {{-- Earnings Section --}}
                <div class="border border-border-gray/80 rounded-card overflow-hidden shadow-subtle">
                    <div class="bg-fog-white/60 px-4 py-2.5 border-b border-border-gray/60 flex items-center justify-between">
                        <span class="text-[11px] font-bold text-success uppercase tracking-wider flex items-center gap-1.5">
                            <x-dynamic-component component="lucide-plus-circle" class="w-3.5 h-3.5" />
                            Penghasilan & Tunjangan (Earnings)
                        </span>
                    </div>
                    <table class="w-full text-left border-collapse">
                        <tbody class="divide-y divide-border-gray/40">
                            @foreach ($earnings as $item)
                                <tr class="hover:bg-mist-gray/30 transition-colors">
                                    <td class="px-4 py-2.5 text-body-sm font-medium text-ink-black">{{ $item->label }}</td>
                                    <td class="px-4 py-2.5 text-body-sm text-right font-semibold text-ink-black tabular-nums">
                                        Rp {{ number_format($item->amount, 2, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-fog-white/80 border-t border-border-gray font-semibold">
                                <td class="px-4 py-3 text-body-sm text-slate-gray">Total Penghasilan Kotor (Gross)</td>
                                <td class="px-4 py-3 text-body-sm text-right font-bold text-success tabular-nums">
                                    Rp {{ number_format($run->gross_salary, 2, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- Deductions Section --}}
                <div class="border border-border-gray/80 rounded-card overflow-hidden shadow-subtle">
                    <div class="bg-fog-white/60 px-4 py-2.5 border-b border-border-gray/60 flex items-center justify-between">
                        <span class="text-[11px] font-bold text-danger uppercase tracking-wider flex items-center gap-1.5">
                            <x-dynamic-component component="lucide-minus-circle" class="w-3.5 h-3.5" />
                            Potongan & Pajak PPh 21 (Deductions)
                        </span>
                    </div>
                    <table class="w-full text-left border-collapse">
                        <tbody class="divide-y divide-border-gray/40">
                            @foreach ($deductions as $item)
                                <tr class="hover:bg-mist-gray/30 transition-colors">
                                    <td class="px-4 py-2.5 text-body-sm font-medium text-ink-black">
                                        {{ $item->label }}
                                        @if (str_contains($item->label, 'PPh21'))
                                            <span class="text-caption font-normal text-slate-gray">(TER Kategori {{ $run->ter_category_used }})</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2.5 text-body-sm text-right font-semibold text-danger tabular-nums">
                                        Rp {{ number_format($item->amount, 2, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-fog-white/80 border-t border-border-gray font-semibold">
                                <td class="px-4 py-3 text-body-sm text-slate-gray">Total Potongan (Deductions)</td>
                                <td class="px-4 py-3 text-body-sm text-right font-bold text-danger tabular-nums">
                                    Rp {{ number_format($run->total_deduction, 2, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- Take Home Pay (Net Salary) Callout Banner --}}
            <div class="mx-6 mb-6 p-5 rounded-card bg-primary text-paper-white flex flex-col sm:flex-row sm:items-center justify-between gap-2 shadow-subtle">
                <div>
                    <span class="text-[11px] uppercase tracking-wider font-semibold opacity-80 block">Gaji Bersih Diterima</span>
                    <h3 class="text-heading-sm font-bold">TOTAL TAKE HOME PAY</h3>
                </div>
                <span class="text-heading font-bold tabular-nums tracking-tight">
                    Rp {{ number_format($run->net_salary, 2, ',', '.') }}
                </span>
            </div>

            {{-- Footer Note --}}
            <div class="px-6 py-4 bg-fog-white/60 border-t border-border-gray/60 text-center">
                <p class="text-[11px] text-slate-gray italic">
                    Dokumen slip gaji ini diterbitkan secara otomatis melalui sistem ERP dan sah tanpa tanda tangan basah.
                </p>
            </div>
        </div>
    </div>
</x-app-layout>
