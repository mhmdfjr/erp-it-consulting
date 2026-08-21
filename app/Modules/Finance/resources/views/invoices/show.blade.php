<x-app-layout>
    <x-slot name="header">
        Detail Faktur: {{ $invoice->invoice_number }}
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

    @php
        $totalPaid = $invoice->payments->sum('amount');
        $remaining = max(0, (float) bcsub((string) $invoice->amount, (string) $totalPaid, 2));
        $isOverdue = $invoice->due_date && $invoice->due_date->isPast() && $invoice->status !== 'paid';
        $canRecordPayment = auth()->user()->can('finance.invoice.pay', $invoice) && $invoice->status !== 'paid' && $remaining > 0;
    @endphp

    <div class="w-full space-y-6">
        {{-- Top Summary Header Card --}}
        <div class="bg-paper-white border border-border-gray/80 rounded-card p-6 shadow-subtle flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="h-12 w-12 rounded-card bg-primary-tint text-primary flex items-center justify-center shrink-0 shadow-subtle">
                    <x-dynamic-component component="lucide-file-text" class="w-6 h-6" />
                </div>
                <div>
                    <div class="flex items-center gap-3">
                        <h2 class="font-mono text-heading-sm font-bold text-ink-black tracking-tight tabular-nums">
                            {{ $invoice->invoice_number }}
                        </h2>
                        <x-badge
                            :status="match (true) {
                                $invoice->status === 'paid' => 'success',
                                $isOverdue => 'danger',
                                default => 'warning'
                            }"
                            variant="solid"
                        >
                            {{ $isOverdue ? 'Overdue' : ucfirst($invoice->status) }}
                        </x-badge>
                    </div>
                    <p class="text-caption text-slate-gray mt-1 flex flex-wrap items-center gap-2 font-medium">
                        <span>Customer: <strong class="text-ink-black">{{ $invoice->salesOrder?->customer?->name ?? 'Customer Umum' }}</strong></span>
                        <span>•</span>
                        <span>Pesanan:
                            <a href="{{ route('sales.orders.show', $invoice->salesOrder) }}" class="text-primary hover:underline font-semibold font-mono">
                                #{{ $invoice->salesOrder?->order_number }}
                            </a>
                        </span>
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2.5 self-end md:self-auto">
                <x-button variant="secondary" size="sm" href="{{ route('finance.invoices.index') }}" class="gap-1.5">
                    <x-dynamic-component component="lucide-arrow-left" class="w-4 h-4" />
                    <span>Kembali</span>
                </x-button>
            </div>
        </div>

        {{-- Metrics & Payment Split Grid --}}
        <div class="grid grid-cols-1 {{ $canRecordPayment ? 'lg:grid-cols-12' : '' }} gap-6 items-start">
            {{-- Left Column: Financial Card Details --}}
            <div class="{{ $canRecordPayment ? 'lg:col-span-7' : 'w-full' }} bg-paper-white border border-border-gray/80 rounded-card p-6 shadow-subtle">
                <div class="border-b border-border-gray/60 pb-3 mb-5">
                    <h3 class="text-heading-sm font-semibold text-ink-black tracking-tight">Rincian Finansial Faktur</h3>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="p-4 bg-fog-white/60 border border-border-gray/60 rounded-input">
                        <span class="text-[11px] font-bold text-ash-gray uppercase tracking-wider block">Total Tagihan (Invoice)</span>
                        <p class="text-body font-bold text-ink-black mt-1 tabular-nums">
                            Rp {{ number_format($invoice->amount, 2, ',', '.') }}
                        </p>
                    </div>

                    <div class="p-4 bg-fog-white/60 border border-border-gray/60 rounded-input">
                        <span class="text-[11px] font-bold text-ash-gray uppercase tracking-wider block">Sisa Belum Lunas</span>
                        <p class="text-body font-bold mt-1 tabular-nums {{ $remaining > 0 ? 'text-danger' : 'text-success' }}">
                            Rp {{ number_format($remaining, 2, ',', '.') }}
                        </p>
                    </div>

                    <div class="p-4 bg-fog-white/60 border border-border-gray/60 rounded-input">
                        <span class="text-[11px] font-bold text-ash-gray uppercase tracking-wider block">Tanggal Penerbitan</span>
                        <p class="text-body-sm font-medium text-ink-black mt-1 tabular-nums">
                            {{ $invoice->invoice_date ? $invoice->invoice_date->format('d F Y') : '-' }}
                        </p>
                    </div>

                    <div class="p-4 bg-fog-white/60 border border-border-gray/60 rounded-input">
                        <span class="text-[11px] font-bold text-ash-gray uppercase tracking-wider block">Batas Jatuh Tempo</span>
                        <p class="text-body-sm font-semibold {{ $isOverdue ? 'text-danger' : 'text-ink-black' }} mt-1 tabular-nums">
                            {{ $invoice->due_date ? $invoice->due_date->format('d F Y') : '-' }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Right Column: Record Payment Form --}}
            @if ($canRecordPayment)
                <div class="lg:col-span-5 bg-paper-white border border-border-gray/80 rounded-card p-6 shadow-subtle">
                    <div class="border-b border-border-gray/60 pb-3 mb-5 flex items-center justify-between">
                        <h3 class="text-heading-sm font-semibold text-ink-black tracking-tight">Catat Penerimaan Kas</h3>
                        <span class="h-2 w-2 rounded-full bg-primary animate-pulse"></span>
                    </div>

                    <form method="POST" action="{{ route('finance.invoices.payments.store', $invoice) }}" class="space-y-4">
                        @csrf

                        {{-- Tanggal Bayar --}}
                        <div>
                            <label for="payment_date" class="block text-label font-medium text-slate-gray mb-1.5">
                                Tanggal Penerimaan <span class="text-danger">*</span>
                            </label>
                            <input
                                type="date"
                                name="payment_date"
                                id="payment_date"
                                required
                                value="{{ old('payment_date', now()->format('Y-m-d')) }}"
                                class="w-full rounded-input border bg-paper-white px-3.5 py-2 text-body-sm text-ink-black transition focus:ring-0 focus:outline-none focus:shadow-focus-ring {{ $errors->has('payment_date') ? 'border-danger focus:border-danger' : 'border-border-gray focus:border-primary' }}"
                            >
                            @error('payment_date')
                                <p class="mt-1.5 text-caption font-medium text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Jumlah Bayar --}}
                        <div>
                            <label for="amount" class="block text-label font-medium text-slate-gray mb-1.5">
                                Nominal Pembayaran (Rp) <span class="text-danger">*</span>
                            </label>
                            <input
                                type="number"
                                step="0.01"
                                max="{{ $remaining }}"
                                min="0.01"
                                name="amount"
                                id="amount"
                                required
                                value="{{ old('amount', $remaining) }}"
                                class="w-full rounded-input border bg-paper-white px-3.5 py-2 text-body-sm font-bold text-ink-black tabular-nums transition focus:ring-0 focus:outline-none focus:shadow-focus-ring {{ $errors->has('amount') ? 'border-danger focus:border-danger' : 'border-border-gray focus:border-primary' }}"
                            >
                            @error('amount')
                                <p class="mt-1.5 text-caption font-medium text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Metode Pembayaran --}}
                        <div>
                            <label for="payment_method" class="block text-label font-medium text-slate-gray mb-1.5">
                                Metode Kas / Transfer <span class="text-danger">*</span>
                            </label>
                            <select
                                name="payment_method"
                                id="payment_method"
                                class="w-full rounded-input border bg-paper-white px-3.5 py-2 text-body-sm text-ink-black transition focus:ring-0 focus:outline-none focus:border-primary focus:shadow-focus-ring"
                            >
                                <option value="transfer">Bank Transfer</option>
                                <option value="cash">Tunai / Cash</option>
                            </select>
                        </div>

                        {{-- Nomor Referensi --}}
                        <div>
                            <label for="reference_number" class="block text-label font-medium text-slate-gray mb-1.5">
                                No. Bukti Transfer / Referensi (Opsional)
                            </label>
                            <input
                                type="text"
                                name="reference_number"
                                id="reference_number"
                                placeholder="Contoh: TRF-BCA-981244"
                                value="{{ old('reference_number') }}"
                                class="w-full rounded-input border border-border-gray bg-paper-white px-3.5 py-2 text-body-sm text-ink-black placeholder-ash-gray transition focus:ring-0 focus:outline-none focus:border-primary focus:shadow-focus-ring"
                            >
                        </div>

                        <div class="pt-2">
                            <x-button type="submit" variant="primary" size="md" class="w-full justify-center gap-1.5">
                                <x-dynamic-component component="lucide-check-circle" class="w-4 h-4" />
                                <span>Catat Pembayaran</span>
                            </x-button>
                        </div>
                    </form>
                </div>
            @endif
        </div>

        {{-- Payment History Card --}}
        <div class="bg-paper-white border border-border-gray/80 rounded-card shadow-subtle overflow-hidden">
            <div class="px-6 py-4 border-b border-border-gray/60 flex items-center justify-between">
                <h3 class="text-heading-sm font-semibold text-ink-black tracking-tight">Riwayat Penerimaan Pembayaran</h3>
                <span class="text-caption font-semibold text-slate-gray tabular-nums">
                    Total {{ $invoice->payments->count() }} Transaksi
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-border-gray/60 bg-fog-white/60">
                            <th scope="col" class="px-6 py-3.5 text-[10px] font-bold text-ash-gray uppercase tracking-wider">Tanggal Bayar</th>
                            <th scope="col" class="px-6 py-3.5 text-[10px] font-bold text-ash-gray uppercase tracking-wider">Metode</th>
                            <th scope="col" class="px-6 py-3.5 text-[10px] font-bold text-ash-gray uppercase tracking-wider">No. Bukti / Ref</th>
                            <th scope="col" class="px-6 py-3.5 text-[10px] font-bold text-ash-gray uppercase tracking-wider text-right">Jumlah Diterima</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-gray/50">
                        @forelse ($invoice->payments as $payment)
                            <tr class="hover:bg-mist-gray/40 transition-colors">
                                <td class="px-6 py-4 text-body-sm font-medium text-ink-black tabular-nums">
                                    {{ $payment->payment_date ? $payment->payment_date->format('d M Y') : '-' }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-1.5 text-caption font-semibold text-slate-gray uppercase tracking-wider bg-fog-white border border-border-gray/80 px-2.5 py-0.5 rounded-badge">
                                        {{ $payment->payment_method ?? 'Transfer' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 font-mono text-caption text-slate-gray tabular-nums">
                                    {{ $payment->reference_number ?: '-' }}
                                </td>
                                <td class="px-6 py-4 text-body-sm font-bold text-right text-success tabular-nums">
                                    Rp {{ number_format($payment->amount, 2, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-slate-gray text-body-sm">
                                    Belum ada pembayaran yang tercatat untuk faktur ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($invoice->payments->isNotEmpty())
                        <tfoot>
                            <tr class="font-semibold bg-fog-white/80 border-t-2 border-border-gray">
                                <td colspan="3" class="px-6 py-4 text-right text-caption uppercase tracking-wider text-slate-gray font-bold">
                                    Total Terbayar
                                </td>
                                <td class="px-6 py-4 text-right text-body-sm font-bold text-success tabular-nums">
                                    Rp {{ number_format($totalPaid, 2, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
