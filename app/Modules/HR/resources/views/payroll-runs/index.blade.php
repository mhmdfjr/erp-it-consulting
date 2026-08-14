<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h1 class="text-heading font-medium text-ink-black">Proses Gaji</h1>
            @can('hr.payroll.process')
                <x-button variant="primary" href="{{ route('hr.payroll-runs.create') }}">
                    + Buat Periode Gaji
                </x-button>
            @endcan
        </div>
    </x-slot>

    <x-data-table :headers="['Periode', 'Jumlah Employee Diproses', 'Status', 'Aksi']" :empty="$periods->isEmpty()">
        <x-slot name="emptyState">
            <div class="flex flex-col items-center gap-2">
                <p class="text-heading-sm">Belum ada periode gaji</p>
                <p class="text-body-sm text-slate-gray">Buat periode pertama untuk mulai proses gaji.</p>
                <x-button variant="primary" href="{{ route('hr.payroll-runs.create') }}">+ Buat Periode Gaji</x-button>
            </div>
        </x-slot>

        @foreach ($periods as $period)
            <tr class="hover:bg-mist-gray">
                <td class="px-4 py-3">{{ \Carbon\Carbon::create($period->period_year, $period->period_month, 1)->translatedFormat('F Y') }}</td>
                <td class="px-4 py-3 tabular-nums">{{ $period->payroll_runs_count }}</td>
                <td class="px-4 py-3">
                    <x-badge status="{{ match($period->status) {
                        'draft' => 'info',
                        'processed' => 'warning',
                        'paid' => 'success',
                    } }}">
                        {{ ucfirst($period->status) }}
                    </x-badge>
                </td>
                <td class="px-4 py-3">
                    <a href="{{ route('hr.payroll-runs.show', $period) }}" class="text-info hover:opacity-70">
                        <x-lucide-eye class="w-4 h-4" />
                    </a>
                </td>
            </tr>
        @endforeach

        <x-slot name="pagination">
            {{ $periods->links() }}
        </x-slot>
    </x-data-table>
</x-app-layout>
