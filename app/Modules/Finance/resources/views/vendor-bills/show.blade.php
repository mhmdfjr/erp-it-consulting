<x-app-layout>
    <x-slot name="header">
        Detail Tagihan: {{ $vendorBill->bill_number }}
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

    <div class="w-full space-y-6">
        {{-- Top Summary Card --}}
        <div class="bg-paper-white border border-border-gray/80 rounded-card p-6 shadow-subtle flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="h-12 w-12 rounded-card bg-primary-tint text-primary flex items-center justify-center shrink-0 shadow-subtle">
                    <x-dynamic-component component="lucide-file-check" class="w-6 h-6" />
                </div>
                <div>
                    <div class="flex items-center gap-3">
                        <h2 class="font-mono text-heading-sm font-bold text-ink-black tracking-tight tabular-nums">
                            {{ $vendorBill->bill_number }}
                        </h2>
                        <x-badge
                            :status="match ($vendorBill->status) {
                                'paid'     => 'success',
                                'approved' => 'warning',
                                'unpaid'   => 'warning',
                                'void'     => 'danger',
                                default    => 'info'
                            }"
                            variant="solid"
                        >
                            {{ ucfirst($vendorBill->status) }}
                        </x-badge>
                    </div>
                    <p class="text-caption text-slate-gray mt-1 flex items-center gap-2 font-medium">
                        <span>Vendor: <strong class="text-ink-black">{{ $vendorBill->vendor?->name ?? '-' }}</strong></span>
                        <span>•</span>
                        <span>Terbit: <strong class="text-ink-black">{{ $vendorBill->bill_date ? $vendorBill->bill_date->format('d M Y') : '-' }}</strong></span>
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2.5 self-end md:self-auto">
                <x-button variant="secondary" size="sm" href="{{ route('finance.vendor-bills.index') }}" class="gap-1.5">
                    <x-dynamic-component component="lucide-arrow-left" class="w-4 h-4" />
                    <span>Kembali</span>
                </x-button>
            </div>
        </div>

        @php
            $canPay = auth()->user()->can('finance.vendorbill.pay') && ($vendorBill->status === 'unpaid' || $vendorBill->status === 'draft');
        @endphp

        {{-- Details & Payment Layout --}}
        <div class="grid grid-cols-1 {{ $canPay ? 'lg:grid-cols-12' : '' }} gap-6 items-start">
            {{-- Left/Main Column: Financial & Account Info --}}
            <div class="{{ $canPay ? 'lg:col-span-7' : 'w-full' }} bg-paper-white border border-border-gray/80 rounded-card p-6 shadow-subtle">
                <div class="border-b border-border-gray/60 pb-3 mb-5">
                    <h3 class="text-heading-sm font-semibold text-ink-black tracking-tight">Rincian Finansial Faktur</h3>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="p-4 bg-fog-white/60 border border-border-gray/60 rounded-input">
                        <span class="text-[11px] font-bold text-ash-gray uppercase tracking-wider block">Akun Alokasi Biaya</span>
                        <p class="text-body-sm font-semibold text-ink-black mt-1">
                            {{ $vendorBill->account?->code ?? '-' }} - {{ $vendorBill->account?->name ?? '-' }}
                        </p>
                    </div>

                    <div class="p-4 bg-fog-white/60 border border-border-gray/60 rounded-input">
                        <span class="text-[11px] font-bold text-ash-gray uppercase tracking-wider block">Total Tagihan (Payable)</span>
                        <p class="text-body font-bold text-primary mt-1 tabular-nums">
                            Rp {{ number_format($vendorBill->amount, 2, ',', '.') }}
                        </p>
                    </div>

                    <div class="p-4 bg-fog-white/60 border border-border-gray/60 rounded-input">
                        <span class="text-[11px] font-bold text-ash-gray uppercase tracking-wider block">Tanggal Bill Terbit</span>
                        <p class="text-body-sm font-medium text-ink-black mt-1 tabular-nums">
                            {{ $vendorBill->bill_date ? $vendorBill->bill_date->format('d F Y') : '-' }}
                        </p>
                    </div>

                    <div class="p-4 bg-fog-white/60 border border-border-gray/60 rounded-input">
                        <span class="text-[11px] font-bold text-ash-gray uppercase tracking-wider block">Batas Jatuh Tempo</span>
                        <p class="text-body-sm font-semibold {{ $vendorBill->due_date && $vendorBill->due_date->isPast() && $vendorBill->status !== 'paid' ? 'text-danger' : 'text-ink-black' }} mt-1 tabular-nums">
                            {{ $vendorBill->due_date ? $vendorBill->due_date->format('d F Y') : '-' }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Right Column: Payment Settlement Action --}}
            @if ($canPay)
                <div class="lg:col-span-5 bg-paper-white border border-border-gray/80 rounded-card p-6 shadow-subtle">
                    <div class="border-b border-border-gray/60 pb-3 mb-5 flex items-center justify-between">
                        <h3 class="text-heading-sm font-semibold text-ink-black tracking-tight">Pelunasan Tagihan</h3>
                        <span class="h-2 w-2 rounded-full bg-warning"></span>
                    </div>

                    <form method="POST" action="{{ route('finance.vendor-bills.mark-as-paid', $vendorBill) }}" class="space-y-4">
                        @csrf

                        <div>
                            <label for="payment_account_id" class="block text-label font-medium text-slate-gray mb-1.5">
                                Sumber Akun Kas / Bank <span class="text-danger">*</span>
                            </label>
                            <select
                                name="payment_account_id"
                                id="payment_account_id"
                                required
                                class="w-full rounded-input border bg-paper-white px-3.5 py-2.5 text-body-sm text-ink-black transition focus:ring-0 focus:outline-none focus:shadow-focus-ring {{ $errors->has('payment_account_id') ? 'border-danger focus:border-danger' : 'border-border-gray focus:border-primary' }}"
                            >
                                <option value="">-- Pilih Akun Kas/Bank Pembayar --</option>
                                @foreach ($cashAndBankAccounts as $account)
                                    <option value="{{ $account->id }}">
                                        {{ $account->code }} - {{ $account->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('payment_account_id')
                                <p class="mt-1.5 text-caption font-medium text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="p-3 bg-primary-tint/50 border border-primary/20 rounded-input">
                            <p class="text-[11px] text-primary leading-tight font-medium">
                                Pelunasan ini akan otomatis membuat entri jurnal pengeluaran kas (Debit Utang Usaha, Kredit Kas/Bank).
                            </p>
                        </div>

                        <div class="pt-2">
                            <x-button variant="primary" type="submit" size="md" class="w-full justify-center gap-1.5">
                                <x-dynamic-component component="lucide-check-circle" class="w-4 h-4" />
                                <span>Konfirmasi Pembayaran Lunas</span>
                            </x-button>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
