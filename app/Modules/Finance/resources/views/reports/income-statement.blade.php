{{-- app/Modules/Finance/resources/views/reports/income-statement.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="text-heading font-medium text-ink-black">Laporan Laba Rugi</h2>
    </x-slot>

    <div class="mx-auto space-y-6">

        <form method="GET" class="max-w-3xl flex gap-3">
            <select name="month" class="border border-border-gray w-full rounded-input px-3 py-2">
                @foreach(range(1, 12) as $m)
                    <option value="{{ $m }}" @selected($m == $month)>
                        {{ \Carbon\Carbon::create(null, $m)->translatedFormat('F') }}
                    </option>
                @endforeach
            </select>
            <select name="year" class="border border-border-gray w-full rounded-input px-3 py-2">
                @foreach(range(now()->year - 2, now()->year) as $y)
                    <option value="{{ $y }}" @selected($y == $year)>{{ $y }}</option>
                @endforeach
            </select>
            <x-button variant="primary" type="submit">Tampilkan</x-button>
        </form>

        <div class="bg-paper-white border border-border-gray rounded-card shadow-subtle overflow-hidden">
            <div class="px-6 py-4 border-b border-border-gray">
                <h3 class="text-heading-sm font-medium text-ink-black">
                    Periode {{ $report['period_start']->translatedFormat('F Y') }}
                </h3>
            </div>

            <div class="px-6 py-5 space-y-6">
                <div>
                    <p class="text-body-sm font-medium uppercase tracking-wide text-slate-gray py-2">Pendapatan</p>
                    <div class="divide-y divide-border-gray">
                        @forelse($report['revenue_lines'] as $line)
                            <div class="flex justify-between items-center py-2 text-body">
                                <span class="text-ink-black">{{ $line['code'] }} &middot; {{ $line['name'] }}</span>
                                <span class="tabular-nums">Rp {{ number_format((float) $line['amount'], 0, ',', '.') }}</span>
                            </div>
                        @empty
                            <p class="text-body-sm text-ash-gray py-2">Tidak ada pendapatan periode ini.</p>
                        @endforelse
                    </div>
                    <div class="flex justify-between items-center py-2 border-t border-border-gray text-body font-medium">
                        <span>Total Pendapatan</span>
                        <span class="tabular-nums">Rp {{ number_format((float) $report['total_revenue'], 0, ',', '.') }}</span>
                    </div>
                </div>

                <div>
                    <p class="text-body-sm font-medium uppercase tracking-wide text-slate-gray py-2">Beban</p>
                    <div class="divide-y divide-border-gray">
                        @forelse($report['expense_lines'] as $line)
                            <div class="flex justify-between items-center py-2 text-body">
                                <span class="text-ink-black">{{ $line['code'] }} &middot; {{ $line['name'] }}</span>
                                <span class="tabular-nums">Rp {{ number_format((float) $line['amount'], 0, ',', '.') }}</span>
                            </div>
                        @empty
                            <p class="text-body-sm text-ash-gray py-2">Tidak ada beban periode ini.</p>
                        @endforelse
                    </div>
                    <div class="flex justify-between items-center py-2 border-t border-border-gray text-body font-medium">
                        <span>Total Beban</span>
                        <span class="tabular-nums">Rp {{ number_format((float) $report['total_expense'], 0, ',', '.') }}</span>
                    </div>
                </div>

            </div>

            <div class="flex justify-between items-center px-6 py-4 border-t-2 border-ink-black bg-fog-white">
                <span class="text-heading-sm font-medium text-ink-black">Laba (Rugi) Bersih</span>
                <span class="text-heading-sm font-medium tabular-nums {{ bccomp($report['net_income'], '0', 2) >= 0 ? 'text-success' : 'text-danger' }}">
                    Rp {{ number_format((float) $report['net_income'], 0, ',', '.') }}
                </span>
            </div>
        </div>
    </div>
</x-app-layout>
