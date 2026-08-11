<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h1 class="text-heading font-medium text-ink-black">
                Payroll - {{ \Carbon\Carbon::create($period->period_year, $period->period_month, 1)->translatedFormat('F Y') }}
            </h1>
            <x-badge status="{{ match($period->status) {
                'draft' => 'info',
                'processed' => 'warning',
                'paid' => 'success',
            } }}">
                {{ ucfirst($period->status) }}
            </x-badge>
        </div>
    </x-slot>

    @if (session('warning'))
        <div class="bg-warning-bg border-l-4 border-warning rounded-input p-4 mb-4">
            <p class="text-body-sm text-ink-black">{{ session('warning') }}</p>
        </div>
    @endif

    @if (session('failedEmployees') && count(session('failedEmployees')) > 0)
        <div class="bg-danger-bg border-l-4 border-danger rounded-input p-4 mb-4">
            <p class="text-body-sm font-medium mb-2">Employee yang gagal diproses:</p>
            <ul class="text-body-sm list-disc list-inside">
                @foreach (session('failedEmployees') as $failure)
                    <li>Employee #{{ $failure['employee_id'] }}: {{ $failure['error'] }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($period->status === 'draft')
        @can('hr.payroll.process')
            @if (count($incompleteAttendance) > 0)
                <div class="bg-warning-bg border-l-4 border-warning rounded-input p-4 mb-4">
                    <p class="text-body-sm font-medium mb-2">
                        Attendance belum lengkap untuk {{ count($incompleteAttendance) }} employee:
                    </p>
                    <ul class="text-body-sm list-disc list-inside mb-3">
                        @foreach ($incompleteAttendance as $item)
                            <li>{{ $item['employee_name'] }} — {{ $item['actual'] }}/{{ $item['expected'] }} hari tercatat</li>
                        @endforeach
                    </ul>
                    <p class="text-caption text-slate-gray mb-3">
                        Hari yang tidak punya record akan dianggap "present" kalau tetap dilanjutkan.
                    </p>
                    <div class="flex gap-2">
                        <form method="POST" action="{{ route('hr.payroll-runs.process', $period) }}">
                            @csrf
                            <input type="hidden" name="force" value="1" />
                            <x-button type="submit" variant="danger">Process Anyway</x-button>
                        </form>
                        <form method="POST" action="{{ route('hr.payroll-runs.cancel', $period) }}"
                            onsubmit="return confirm('Batalkan payroll period ini? Periode akan dihapus, belum ada data yang tersimpan.')">
                            @csrf
                            @method('DELETE')
                            <x-button type="submit" variant="primary">Cancel Draft</x-button>
                        </form>
                    </div>
                </div>
            @else
                <form method="POST" action="{{ route('hr.payroll-runs.process', $period) }}" class="mb-4">
                    @csrf
                    <x-button type="submit" variant="primary">Process Payroll</x-button>
                </form>
            @endif
        @endcan
    @endif

    @if ($period->status === 'processed')
        @can('hr.payroll.pay')
            <form method="POST" action="{{ route('hr.payroll-runs.mark-as-paid', $period) }}" class="mb-4">
                @csrf
                <x-button type="submit" variant="primary">Mark All as Paid</x-button>
            </form>
        @endcan
    @endif

    <x-data-table :headers="['Employee', 'Hari Kerja', 'Absent', 'Base Salary', 'Gross', 'PPh21', 'Net Salary', 'Status', 'Aksi']" :empty="$runs->isEmpty()">
        <x-slot name="emptyState">
            <p class="text-body-sm text-slate-gray">Belum ada payroll run untuk periode ini.</p>
        </x-slot>

        @foreach ($runs as $run)
            <tr class="hover:bg-mist-gray">
                <td class="px-4 py-3">{{ $run->employee->full_name }}</td>
                <td class="px-4 py-3 tabular-nums">{{ $run->working_days }}</td>
                <td class="px-4 py-3 tabular-nums">{{ $run->absent_days }}</td>
                <td class="px-4 py-3 tabular-nums">Rp {{ number_format($run->base_salary, 0, ',', '.') }}</td>
                <td class="px-4 py-3 tabular-nums">Rp {{ number_format($run->gross_salary, 0, ',', '.') }}</td>
                <td class="px-4 py-3 tabular-nums">Rp {{ number_format($run->pph21_deduction, 0, ',', '.') }}</td>
                <td class="px-4 py-3 tabular-nums font-medium">Rp {{ number_format($run->net_salary, 0, ',', '.') }}</td>
                <td class="px-4 py-3">
                    <x-badge status="{{ $run->status === 'paid' ? 'success' : 'info' }}">
                        {{ ucfirst($run->status) }}
                    </x-badge>
                </td>
                <td class="px-4 py-3">
                    <a href="{{ route('hr.payroll-runs.slip', $run) }}" class="text-info hover:opacity-70">
                        <x-lucide-receipt-text class="w-4 h-4" />
                    </a>
                </td>
            </tr>
        @endforeach
    </x-data-table>
</x-app-layout>
