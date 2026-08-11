<x-app-layout>
    <x-slot name="header">
        Slip Gaji - {{ $run->employee->full_name }}
        ({{ \Carbon\Carbon::create($run->payrollPeriod->period_year, $run->payrollPeriod->period_month, 1)->translatedFormat('F Y') }})
    </x-slot>

    <div class="max-w-2xl space-y-6">
        <div class="bg-paper-white border border-border-gray rounded-card p-4">
            <dl class="grid grid-cols-2 gap-y-2 text-body-sm">
                <dt class="text-slate-gray">Nama Employee</dt>
                <dd>{{ $run->employee->full_name }} ({{ $run->employee->employee_code }})</dd>

                <dt class="text-slate-gray">Position</dt>
                <dd>{{ $run->employee->position->title }} - {{ $run->employee->position->department->name }}</dd>

                <dt class="text-slate-gray">PTKP Status</dt>
                <dd>{{ $run->employee->ptkp_status }}</dd>

                <dt class="text-slate-gray">Status Payroll Run</dt>
                <dd>
                    <x-badge status="{{ $run->status === 'paid' ? 'success' : 'info' }}">
                        {{ ucfirst($run->status) }}
                    </x-badge>
                </dd>
            </dl>
        </div>

        @if ($run->absent_days > 0)
            <div class="bg-warning-bg border-l-4 border-warning rounded-input p-4">
                <p class="text-body-sm">
                    Base salary di-prorate: {{ $run->absent_days }} hari absent dari
                    {{ $run->working_days }} hari kerja periode ini
                    ({{ $run->working_days - $run->absent_days }}/{{ $run->working_days }} hari efektif).
                    Tunjangan (earning component lain) tetap dibayar penuh, tidak ikut prorate.
                </p>
            </div>
        @endif

        <div class="bg-paper-white border border-border-gray rounded-card overflow-hidden">
            <div class="bg-fog-white border-b border-border-gray px-4 py-3">
                <p class="text-label text-slate-gray">Earning</p>
            </div>
            <table class="w-full">
                <tbody class="divide-y divide-border-gray">
                    @foreach ($earnings as $item)
                        <tr>
                            <td class="px-4 py-3 text-body-sm">{{ $item->label }}</td>
                            <td class="px-4 py-3 text-body-sm text-right tabular-nums">
                                Rp {{ number_format($item->amount, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                    <tr class="bg-mist-gray">
                        <td class="px-4 py-3 text-body-sm font-medium">Gross Salary</td>
                        <td class="px-4 py-3 text-body-sm font-medium text-right tabular-nums">
                            Rp {{ number_format($run->gross_salary, 0, ',', '.') }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="bg-paper-white border border-border-gray rounded-card overflow-hidden">
            <div class="bg-fog-white border-b border-border-gray px-4 py-3">
                <p class="text-label text-slate-gray">Deduction</p>
            </div>
            <table class="w-full">
                <tbody class="divide-y divide-border-gray">
                    @foreach ($deductions as $item)
                        <tr>
                            <td class="px-4 py-3 text-body-sm">
                                {{ $item->label }}
                                @if (str_contains($item->label, 'PPh21'))
                                    <span class="text-caption text-slate-gray">
                                        (TER Kategori {{ $run->ter_category_used }})
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-body-sm text-right tabular-nums">
                                Rp {{ number_format($item->amount, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                    <tr class="bg-mist-gray">
                        <td class="px-4 py-3 text-body-sm font-medium">Total Deduction</td>
                        <td class="px-4 py-3 text-body-sm font-medium text-right tabular-nums">
                            Rp {{ number_format($run->total_deduction, 0, ',', '.') }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="bg-ink-black rounded-card p-4 flex items-center justify-between">
            <span class="text-body text-paper-white font-medium">Net Salary</span>
            <span class="text-heading-sm text-paper-white font-medium tabular-nums">
                Rp {{ number_format($run->net_salary, 0, ',', '.') }}
            </span>
        </div>

        <x-button variant="secondary" href="{{ route('hr.payroll-runs.show', $run->payroll_period_id) }}">
            Kembali ke Payroll Run
        </x-button>
    </div>
</x-app-layout>
