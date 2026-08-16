{{-- app/Modules/Finance/resources/views/reports/balance-sheet.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="text-heading font-medium text-ink-black">Neraca</h2>
    </x-slot>

    <div class="mx-auto space-y-6">

        <form method="GET" class="max-w-3xl flex gap-3">
            <input type="date" name="as_of" value="{{ $report['as_of_date']->toDateString() }}"
                class="border border-border-gray rounded-input px-3 py-2">
            <x-button variant="primary" type="submit" >Tampilkan</x-button>
        </form>

        @unless($report['is_balanced'])
            <div class="rounded-input bg-danger-bg border border-danger/20 text-danger px-4 py-3 text-body-sm">
                <span class="font-medium">Peringatan:</span>
                Total Aset (Rp {{ number_format((float) $report['total_asset'], 0, ',', '.') }})
                tidak sama dengan Total Liabilitas + Ekuitas
                (Rp {{ number_format((float) $report['total_liability_and_equity'], 0, ',', '.') }}).
                Ada kemungkinan data tidak konsisten, perlu diperiksa.
            </div>
        @endunless

        <div class="bg-paper-white border border-border-gray rounded-card shadow-subtle overflow-hidden">
            <div class="px-6 py-4 border-b border-border-gray">
                <h3 class="text-heading-sm font-medium text-ink-black">
                    Per {{ $report['as_of_date']->translatedFormat('d F Y') }}
                </h3>
            </div>

            <div class="px-6 py-5 space-y-6">

                <div>
                    <p class="text-body-sm font-medium uppercase tracking-wide text-slate-gray py-2">Aset</p>
                    <div class="divide-y divide-border-gray">
                        @foreach($report['asset_lines'] as $line)
                            <div class="flex justify-between items-center py-2 text-body">
                                <span class="text-ink-black">{{ $line['code'] }} &middot; {{ $line['name'] }}</span>
                                <span class="tabular-nums">Rp {{ number_format((float) $line['balance'], 0, ',', '.') }}</span>
                            </div>
                        @endforeach
                    </div>
                    <div class="flex justify-between items-center py-2 border-t border-border-gray text-body font-medium">
                        <span>Total Aset</span>
                        <span class="tabular-nums">Rp {{ number_format((float) $report['total_asset'], 0, ',', '.') }}</span>
                    </div>
                </div>

                <div>
                    <p class="text-body-sm font-medium uppercase tracking-wide text-slate-gray py-2">Liabilitas</p>
                    <div class="divide-y divide-border-gray">
                        @foreach($report['liability_lines'] as $line)
                            <div class="flex justify-between items-center py-2 text-body">
                                <span class="text-ink-black">{{ $line['code'] }} &middot; {{ $line['name'] }}</span>
                                <span class="tabular-nums">Rp {{ number_format((float) $line['balance'], 0, ',', '.') }}</span>
                            </div>
                        @endforeach
                    </div>
                    <div class="flex justify-between items-center py-2 border-t border-border-gray text-body font-medium">
                        <span>Total Liabilitas</span>
                        <span class="tabular-nums">Rp {{ number_format((float) $report['total_liability'], 0, ',', '.') }}</span>
                    </div>
                </div>

                <div>
                    <p class="text-body-sm font-medium uppercase tracking-wide text-slate-gray mb-2">Ekuitas</p>
                    <div class="divide-y divide-border-gray">
                        @foreach($report['equity_lines'] as $line)
                            <div class="flex justify-between items-center py-2 text-body">
                                <span class="text-ink-black">{{ $line['code'] }} &middot; {{ $line['name'] }}</span>
                                <span class="tabular-nums">Rp {{ number_format((float) $line['balance'], 0, ',', '.') }}</span>
                            </div>
                        @endforeach
                        <div class="flex justify-between items-center py-2 text-body">
                            <span class="text-ink-black">Laba (Rugi) Berjalan <span class="text-caption text-ash-gray">(Belum Ditutup)</span></span>
                            <span class="tabular-nums">Rp {{ number_format((float) $report['net_income_to_date'], 0, ',', '.') }}</span>
                        </div>
                    </div>
                    <div class="flex justify-between items-center py-2 border-t border-border-gray text-body font-medium">
                        <span>Total Ekuitas</span>
                        <span class="tabular-nums">Rp {{ number_format((float) bcadd($report['total_equity'], $report['net_income_to_date'], 2), 0, ',', '.') }}</span>
                    </div>
                </div>

            </div>

            <div class="flex justify-between items-center px-6 py-4 border-t-2 border-ink-black bg-fog-white">
                <span class="text-heading-sm font-medium text-ink-black">Total Liabilitas + Ekuitas</span>
                <span class="text-heading-sm font-medium tabular-nums text-ink-black">
                    Rp {{ number_format((float) $report['total_liability_and_equity'], 0, ',', '.') }}
                </span>
            </div>
        </div>

    </div>
</x-app-layout>
