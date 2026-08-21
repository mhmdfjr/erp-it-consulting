{{-- app/Modules/Finance/resources/views/reports/balance-sheet.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        Laporan Neraca Keuangan (Balance Sheet)
    </x-slot>

    <div class="max-w-5xl mx-auto space-y-6">
        {{-- Filter Bar Card --}}
        <div class="bg-paper-white border border-border-gray/80 rounded-card p-4 shadow-subtle flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <form method="GET" class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
                <div class="w-full sm:w-56">
                    <input
                        type="date"
                        name="as_of"
                        value="{{ $report['as_of_date']->toDateString() }}"
                        class="w-full rounded-input border border-border-gray bg-paper-white px-3.5 py-2 text-body-sm text-ink-black transition focus:ring-0 focus:outline-none focus:border-primary focus:shadow-focus-ring"
                    >
                </div>

                <x-button variant="primary" size="md" type="submit" class="gap-1.5 w-full sm:w-auto">
                    <x-dynamic-component component="lucide-filter" class="w-4 h-4" />
                    <span>Tampilkan</span>
                </x-button>
            </form>

            <div class="hidden lg:flex items-center gap-2 text-caption text-slate-gray">
                <x-dynamic-component component="lucide-calendar" class="w-4 h-4 text-primary" />
                <span>Posisi Saldo Per: <strong>{{ $report['as_of_date']->translatedFormat('d F Y') }}</strong></span>
            </div>
        </div>

        {{-- Unbalanced Alert Banner --}}
        @unless($report['is_balanced'])
            <div class="rounded-card bg-danger-bg border border-danger/30 text-danger p-4 shadow-subtle flex items-start gap-3">
                <x-dynamic-component component="lucide-alert-triangle" class="w-5 h-5 shrink-0 mt-0.5" />
                <div>
                    <h4 class="font-bold text-body-sm">Peringatan: Neraca Saldo Tidak Seimbang (Unbalanced)!</h4>
                    <p class="text-caption mt-0.5 leading-relaxed">
                        Total Aset (<strong>Rp {{ number_format((float) $report['total_asset'], 2, ',', '.') }}</strong>) tidak sama dengan Total Liabilitas + Ekuitas (<strong>Rp {{ number_format((float) $report['total_liability_and_equity'], 2, ',', '.') }}</strong>). Periksa entri jurnal yang belum seimbang atau pos penyesuaian.
                    </p>
                </div>
            </div>
        @endunless

        {{-- Balance Sheet Paper Card --}}
        <div class="bg-paper-white border border-border-gray/80 rounded-card shadow-subtle overflow-hidden">
            {{-- Document Header --}}
            <div class="px-6 py-5 border-b border-border-gray/60 bg-fog-white/60 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-card bg-primary-tint text-primary flex items-center justify-center shrink-0 shadow-subtle">
                        <x-dynamic-component component="lucide-scale" class="w-5 h-5" />
                    </div>
                    <div>
                        <h3 class="text-heading-sm font-semibold text-ink-black tracking-tight">
                            Laporan Posisi Keuangan (Neraca)
                        </h3>
                        <p class="text-caption text-slate-gray mt-0.5">
                            Per Tanggal {{ $report['as_of_date']->translatedFormat('d F Y') }}
                        </p>
                    </div>
                </div>

                <x-badge :status="$report['is_balanced'] ? 'success' : 'danger'" variant="solid">
                    {{ $report['is_balanced'] ? 'Status: Balanced' : 'Status: Unbalanced' }}
                </x-badge>
            </div>

            <div class="p-6 space-y-8">
                {{-- 1. Pos Aset --}}
                <div>
                    <div class="flex items-center justify-between pb-2 mb-3 border-b border-border-gray/60">
                        <span class="text-[11px] font-bold text-primary uppercase tracking-wider flex items-center gap-1.5">
                            <x-dynamic-component component="lucide-folder-tree" class="w-3.5 h-3.5" />
                            Aset & Aktiva (Assets)
                        </span>
                    </div>

                    <div class="divide-y divide-border-gray/40">
                        @forelse($report['asset_lines'] as $line)
                            <div class="flex justify-between items-center py-2.5 hover:bg-mist-gray/30 px-2 rounded-input transition-colors">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono text-caption font-semibold text-slate-gray bg-fog-white px-2 py-0.5 rounded-input border border-border-gray/60 tabular-nums">
                                        {{ $line['code'] }}
                                    </span>
                                    <span class="text-body-sm font-medium text-ink-black">{{ $line['name'] }}</span>
                                </div>
                                <span class="text-body-sm font-semibold text-ink-black tabular-nums">
                                    Rp {{ number_format((float) $line['balance'], 2, ',', '.') }}
                                </span>
                            </div>
                        @empty
                            <p class="text-caption text-ash-gray py-3 px-2 italic">Tidak ada pos aset tercatat.</p>
                        @endforelse
                    </div>

                    <div class="flex justify-between items-center px-2 pt-3 mt-2 border-t border-border-gray text-body-sm font-bold text-ink-black bg-fog-white/40 rounded-input">
                        <span class="text-slate-gray">Total Aset (Aktiva)</span>
                        <span class="tabular-nums text-primary font-bold">
                            Rp {{ number_format((float) $report['total_asset'], 2, ',', '.') }}
                        </span>
                    </div>
                </div>

                {{-- 2. Pos Liabilitas --}}
                <div>
                    <div class="flex items-center justify-between pb-2 mb-3 border-b border-border-gray/60">
                        <span class="text-[11px] font-bold text-[#c2410c] uppercase tracking-wider flex items-center gap-1.5">
                            <x-dynamic-component component="lucide-receipt" class="w-3.5 h-3.5 text-[#c2410c]" />
                            Kewajiban & Liabilitas (Liabilities)
                        </span>
                    </div>

                    <div class="divide-y divide-border-gray/40">
                        @forelse($report['liability_lines'] as $line)
                            <div class="flex justify-between items-center py-2.5 hover:bg-mist-gray/30 px-2 rounded-input transition-colors">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono text-caption font-semibold text-slate-gray bg-fog-white px-2 py-0.5 rounded-input border border-border-gray/60 tabular-nums">
                                        {{ $line['code'] }}
                                    </span>
                                    <span class="text-body-sm font-medium text-ink-black">{{ $line['name'] }}</span>
                                </div>
                                <span class="text-body-sm font-semibold text-ink-black tabular-nums">
                                    Rp {{ number_format((float) $line['balance'], 2, ',', '.') }}
                                </span>
                            </div>
                        @empty
                            <p class="text-caption text-ash-gray py-3 px-2 italic">Tidak ada pos liabilitas tercatat.</p>
                        @endforelse
                    </div>

                    <div class="flex justify-between items-center px-2 pt-3 mt-2 border-t border-border-gray text-body-sm font-bold text-ink-black bg-fog-white/40 rounded-input">
                        <span class="text-slate-gray">Total Liabilitas (Kewajiban)</span>
                        <span class="tabular-nums text-[#c2410c]">
                            Rp {{ number_format((float) $report['total_liability'], 2, ',', '.') }}
                        </span>
                    </div>
                </div>

                {{-- 3. Pos Ekuitas --}}
                <div>
                    <div class="flex items-center justify-between pb-2 mb-3 border-b border-border-gray/60">
                        <span class="text-[11px] font-bold text-secondary uppercase tracking-wider flex items-center gap-1.5">
                            <x-dynamic-component component="lucide-pie-chart" class="w-3.5 h-3.5" />
                            Ekuitas & Modal (Equity)
                        </span>
                    </div>

                    <div class="divide-y divide-border-gray/40">
                        @foreach($report['equity_lines'] as $line)
                            <div class="flex justify-between items-center py-2.5 hover:bg-mist-gray/30 px-2 rounded-input transition-colors">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono text-caption font-semibold text-slate-gray bg-fog-white px-2 py-0.5 rounded-input border border-border-gray/60 tabular-nums">
                                        {{ $line['code'] }}
                                    </span>
                                    <span class="text-body-sm font-medium text-ink-black">{{ $line['name'] }}</span>
                                </div>
                                <span class="text-body-sm font-semibold text-ink-black tabular-nums">
                                    Rp {{ number_format((float) $line['balance'], 2, ',', '.') }}
                                </span>
                            </div>
                        @endforeach

                        {{-- Current Period Net Income --}}
                        <div class="flex justify-between items-center py-2.5 hover:bg-mist-gray/30 px-2 rounded-input transition-colors bg-primary-tint/30">
                            <div class="flex items-center gap-2">
                                <span class="font-mono text-caption font-semibold text-primary bg-primary-tint px-2 py-0.5 rounded-input border border-primary/20">
                                    P/L
                                </span>
                                <span class="text-body-sm font-semibold text-ink-black">
                                    Laba (Rugi) Berjalan <span class="text-caption font-normal text-slate-gray">(Belum Ditutup)</span>
                                </span>
                            </div>
                            <span class="text-body-sm font-bold text-primary tabular-nums">
                                Rp {{ number_format((float) $report['net_income_to_date'], 2, ',', '.') }}
                            </span>
                        </div>
                    </div>

                    <div class="flex justify-between items-center px-2 pt-3 mt-2 border-t border-border-gray text-body-sm font-bold text-ink-black bg-fog-white/40 rounded-input">
                        <span class="text-slate-gray">Total Ekuitas Bersih</span>
                        <span class="tabular-nums text-secondary font-bold">
                            Rp {{ number_format((float) bcadd($report['total_equity'], $report['net_income_to_date'], 2), 2, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Balance Validation Footer --}}
            <div class="flex flex-col sm:flex-row justify-between items-center px-6 py-5 border-t-2 border-border-gray/80 bg-fog-white/90 gap-2">
                <div>
                    <span class="text-body font-bold text-ink-black block leading-tight">Total Pasiva (Liabilitas + Ekuitas)</span>
                    <span class="text-caption text-slate-gray">Total Claims on Assets</span>
                </div>

                <span class="text-heading font-bold tabular-nums text-ink-black">
                    Rp {{ number_format((float) $report['total_liability_and_equity'], 2, ',', '.') }}
                </span>
            </div>
        </div>
    </div>
</x-app-layout>
