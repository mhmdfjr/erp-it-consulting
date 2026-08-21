{{-- app/Modules/Finance/resources/views/reports/income-statement.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        Laporan Laba Rugi (Income Statement)
    </x-slot>

    <div class="max-w-5xl mx-auto space-y-6">
        {{-- Filter Bar Card --}}
        <div class="bg-paper-white border border-border-gray/80 rounded-card p-4 shadow-subtle flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <form method="GET" class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
                <div class="w-full sm:w-48">
                    <select name="month" class="w-full rounded-input border border-border-gray bg-paper-white px-3.5 py-2 text-body-sm text-ink-black transition focus:ring-0 focus:outline-none focus:border-primary focus:shadow-focus-ring">
                        @foreach(range(1, 12) as $m)
                            <option value="{{ $m }}" @selected($m == $month)>
                                {{ \Carbon\Carbon::create(null, $m)->translatedFormat('F') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="w-full sm:w-32">
                    <select name="year" class="w-full rounded-input border border-border-gray bg-paper-white px-3.5 py-2 text-body-sm text-ink-black transition focus:ring-0 focus:outline-none focus:border-primary focus:shadow-focus-ring">
                        @foreach(range(now()->year - 2, now()->year) as $y)
                            <option value="{{ $y }}" @selected($y == $year)>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>

                <x-button variant="primary" size="md" type="submit" class="gap-1.5 w-full sm:w-auto">
                    <x-dynamic-component component="lucide-filter" class="w-4 h-4" />
                    <span>Tampilkan</span>
                </x-button>
            </form>

            <div class="hidden lg:flex items-center gap-2 text-caption text-slate-gray">
                <x-dynamic-component component="lucide-calendar" class="w-4 h-4 text-primary" />
                <span>Periode: <strong>{{ $report['period_start']->translatedFormat('F Y') }}</strong></span>
            </div>
        </div>

        {{-- Financial Statement Paper Card --}}
        <div class="bg-paper-white border border-border-gray/80 rounded-card shadow-subtle overflow-hidden">
            {{-- Document Header --}}
            <div class="px-6 py-5 border-b border-border-gray/60 bg-fog-white/60 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-card bg-primary-tint text-primary flex items-center justify-center shrink-0 shadow-subtle">
                        <x-dynamic-component component="lucide-trending-up" class="w-5 h-5" />
                    </div>
                    <div>
                        <h3 class="text-heading-sm font-semibold text-ink-black tracking-tight">
                            Laporan Laba Rugi Komprehensif
                        </h3>
                        <p class="text-caption text-slate-gray mt-0.5">
                            Periode Pembukuan {{ $report['period_start']->translatedFormat('F Y') }}
                        </p>
                    </div>
                </div>

                <span class="inline-flex items-center gap-1.5 text-caption font-semibold text-slate-gray bg-paper-white border border-border-gray/80 px-3 py-1 rounded-badge">
                    <x-dynamic-component component="lucide-shield-check" class="w-3.5 h-3.5 text-success" />
                    Buku Besar Terverifikasi
                </span>
            </div>

            <div class="p-6 space-y-8">
                {{-- Pos Pendapatan (Revenue) --}}
                <div>
                    <div class="flex items-center justify-between pb-2 mb-3 border-b border-border-gray/60">
                        <span class="text-[11px] font-bold text-primary uppercase tracking-wider flex items-center gap-1.5">
                            <x-dynamic-component component="lucide-plus-circle" class="w-3.5 h-3.5" />
                            1. Pendapatan Operasional & Usaha
                        </span>
                    </div>

                    <div class="divide-y divide-border-gray/40">
                        @forelse($report['revenue_lines'] as $line)
                            <div class="flex justify-between items-center py-2.5 hover:bg-mist-gray/30 px-2 rounded-input transition-colors">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono text-caption font-semibold text-slate-gray bg-fog-white px-2 py-0.5 rounded-input border border-border-gray/60 tabular-nums">
                                        {{ $line['code'] }}
                                    </span>
                                    <span class="text-body-sm font-medium text-ink-black">{{ $line['name'] }}</span>
                                </div>
                                <span class="text-body-sm font-semibold text-ink-black tabular-nums">
                                    Rp {{ number_format((float) $line['amount'], 2, ',', '.') }}
                                </span>
                            </div>
                        @empty
                            <p class="text-caption text-ash-gray py-3 px-2 italic">Tidak ada pencatatan pos pendapatan pada periode ini.</p>
                        @endforelse
                    </div>

                    <div class="flex justify-between items-center px-2 pt-3 mt-2 border-t border-border-gray text-body-sm font-bold text-ink-black bg-fog-white/40 rounded-input">
                        <span class="text-slate-gray">Total Pendapatan (Gross Revenue)</span>
                        <span class="tabular-nums text-primary">
                            Rp {{ number_format((float) $report['total_revenue'], 2, ',', '.') }}
                        </span>
                    </div>
                </div>

                {{-- Pos Beban (Expenses) --}}
                <div>
                    <div class="flex items-center justify-between pb-2 mb-3 border-b border-border-gray/60">
                        <span class="text-[11px] font-bold text-[#c2410c] uppercase tracking-wider flex items-center gap-1.5">
                            <x-dynamic-component component="lucide-minus-circle" class="w-3.5 h-3.5 text-[#c2410c]" />
                            2. Beban Operasional & Administrasi
                        </span>
                    </div>

                    <div class="divide-y divide-border-gray/40">
                        @forelse($report['expense_lines'] as $line)
                            <div class="flex justify-between items-center py-2.5 hover:bg-mist-gray/30 px-2 rounded-input transition-colors">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono text-caption font-semibold text-slate-gray bg-fog-white px-2 py-0.5 rounded-input border border-border-gray/60 tabular-nums">
                                        {{ $line['code'] }}
                                    </span>
                                    <span class="text-body-sm font-medium text-ink-black">{{ $line['name'] }}</span>
                                </div>
                                <span class="text-body-sm font-semibold text-ink-black tabular-nums">
                                    Rp {{ number_format((float) $line['amount'], 2, ',', '.') }}
                                </span>
                            </div>
                        @empty
                            <p class="text-caption text-ash-gray py-3 px-2 italic">Tidak ada pencatatan pos beban pada periode ini.</p>
                        @endforelse
                    </div>

                    <div class="flex justify-between items-center px-2 pt-3 mt-2 border-t border-border-gray text-body-sm font-bold text-ink-black bg-fog-white/40 rounded-input">
                        <span class="text-slate-gray">Total Beban Operasional</span>
                        <span class="tabular-nums text-[#c2410c]">
                            Rp {{ number_format((float) $report['total_expense'], 2, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Net Income Result Footer --}}
            @php
                $isProfit = bccomp($report['net_income'], '0', 2) >= 0;
            @endphp
            <div class="flex flex-col sm:flex-row justify-between items-center px-6 py-5 border-t-2 border-border-gray/80 bg-fog-white/90 gap-2">
                <div class="flex items-center gap-2.5">
                    <div class="h-8 w-8 rounded-full flex items-center justify-center {{ $isProfit ? 'bg-success-bg text-success' : 'bg-danger-bg text-danger' }}">
                        <x-dynamic-component :component="$isProfit ? 'lucide-arrow-up-right' : 'lucide-arrow-down-right'" class="w-4 h-4" />
                    </div>
                    <div>
                        <span class="text-body font-bold text-ink-black block leading-tight">Laba (Rugi) Bersih Periode Berjalan</span>
                        <span class="text-caption text-slate-gray">Net Profit / Loss Before Tax</span>
                    </div>
                </div>

                <span class="text-heading font-bold tabular-nums {{ $isProfit ? 'text-success' : 'text-danger' }}">
                    Rp {{ number_format((float) $report['net_income'], 2, ',', '.') }}
                </span>
            </div>
        </div>
    </div>
</x-app-layout>
