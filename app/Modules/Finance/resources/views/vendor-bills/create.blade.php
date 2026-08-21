<x-app-layout>
    <x-slot name="header">
        Buat Tagihan Vendor Baru
    </x-slot>

    <div class="max-w-3xl bg-paper-white border border-border-gray/80 rounded-card p-6 shadow-subtle">
        <div class="border-b border-border-gray/60 pb-4 mb-6">
            <h3 class="text-heading-sm font-semibold text-ink-black">Formulir Tagihan Vendor (Vendor Bill)</h3>
            <p class="text-caption text-slate-gray mt-0.5">Catat faktur pembelian masuk dan jadwalkan akun pembebanan akuntansi.</p>
        </div>

        <form method="POST" action="{{ route('finance.vendor-bills.store') }}" class="space-y-5">
            @csrf

            {{-- Vendor Picker --}}
            <div>
                <label for="vendor_id" class="block text-label font-medium text-slate-gray mb-1.5">Pilih Rekanan Vendor <span class="text-danger">*</span></label>
                <select
                    name="vendor_id"
                    id="vendor_id"
                    required
                    class="w-full rounded-input border bg-paper-white px-3.5 py-2.5 text-body text-ink-black transition focus:ring-0 focus:outline-none focus:shadow-focus-ring {{ $errors->has('vendor_id') ? 'border-danger focus:border-danger' : 'border-border-gray focus:border-primary' }}"
                >
                    <option value="">-- Pilih Rekanan Vendor --</option>
                    @foreach ($vendors as $vendor)
                        <option value="{{ $vendor->id }}" @selected(old('vendor_id') == $vendor->id)>
                            {{ $vendor->name }} {{ $vendor->phone ? '('.$vendor->phone.')' : '' }}
                        </option>
                    @endforeach
                </select>
                @error('vendor_id')
                    <p class="mt-1.5 text-caption font-medium text-danger">{{ $message }}</p>
                @enderror
            </div>

            {{-- Account Allocation (Debit) --}}
            <div>
                <label for="account_id" class="block text-label font-medium text-slate-gray mb-1.5">Akun Alokasi Biaya / Aset (Pos Debit) <span class="text-danger">*</span></label>
                <select
                    name="account_id"
                    id="account_id"
                    required
                    class="w-full rounded-input border bg-paper-white px-3.5 py-2.5 text-body text-ink-black transition focus:ring-0 focus:outline-none focus:shadow-focus-ring {{ $errors->has('account_id') ? 'border-danger focus:border-danger' : 'border-border-gray focus:border-primary' }}"
                >
                    <option value="">-- Pilih Akun COA Terkait --</option>
                    @foreach ($accounts as $account)
                        <option value="{{ $account->id }}" @selected(old('account_id') == $account->id)>
                            {{ $account->code }} - {{ $account->name }} ({{ ucfirst($account->account_type) }})
                        </option>
                    @endforeach
                </select>
                @error('account_id')
                    <p class="mt-1.5 text-caption font-medium text-danger">{{ $message }}</p>
                @enderror
            </div>

            {{-- Bill Number --}}
            <div>
                <label for="bill_number" class="block text-label font-medium text-slate-gray mb-1.5">Nomor Referensi Bill / Invoice Vendor <span class="text-danger">*</span></label>
                <input
                    type="text"
                    name="bill_number"
                    id="bill_number"
                    value="{{ old('bill_number') }}"
                    placeholder="Contoh: BILL-2026-008 atau INV/VEND/099"
                    required
                    class="w-full rounded-input border bg-paper-white px-3.5 py-2.5 text-body font-mono text-ink-black placeholder-ash-gray transition focus:ring-0 focus:outline-none focus:shadow-focus-ring {{ $errors->has('bill_number') ? 'border-danger focus:border-danger' : 'border-border-gray focus:border-primary' }}"
                >
                @error('bill_number')
                    <p class="mt-1.5 text-caption font-medium text-danger">{{ $message }}</p>
                @enderror
            </div>

            {{-- Grid: Tanggal Bill & Jatuh Tempo --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="bill_date" class="block text-label font-medium text-slate-gray mb-1.5">Tanggal Penerbitan Bill <span class="text-danger">*</span></label>
                    <input
                        type="date"
                        name="bill_date"
                        id="bill_date"
                        value="{{ old('bill_date', date('Y-m-d')) }}"
                        required
                        class="w-full rounded-input border bg-paper-white px-3.5 py-2.5 text-body text-ink-black transition focus:ring-0 focus:outline-none focus:shadow-focus-ring {{ $errors->has('bill_date') ? 'border-danger focus:border-danger' : 'border-border-gray focus:border-primary' }}"
                    >
                    @error('bill_date')
                        <p class="mt-1.5 text-caption font-medium text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="due_date" class="block text-label font-medium text-slate-gray mb-1.5">Batas Jatuh Tempo Pembayaran <span class="text-danger">*</span></label>
                    <input
                        type="date"
                        name="due_date"
                        id="due_date"
                        value="{{ old('due_date') }}"
                        required
                        class="w-full rounded-input border bg-paper-white px-3.5 py-2.5 text-body text-ink-black transition focus:ring-0 focus:outline-none focus:shadow-focus-ring {{ $errors->has('due_date') ? 'border-danger focus:border-danger' : 'border-border-gray focus:border-primary' }}"
                    >
                    @error('due_date')
                        <p class="mt-1.5 text-caption font-medium text-danger">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Nominal Amount --}}
            <div>
                <label for="amount" class="block text-label font-medium text-slate-gray mb-1.5">Nominal Total Tagihan (Rp) <span class="text-danger">*</span></label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-gray font-semibold text-body-sm">
                        Rp
                    </span>
                    <input
                        type="number"
                        step="0.01"
                        min="0.01"
                        name="amount"
                        id="amount"
                        value="{{ old('amount') }}"
                        placeholder="0.00"
                        required
                        class="w-full pl-10 rounded-input border bg-paper-white px-3.5 py-2.5 text-body font-bold text-ink-black tabular-nums placeholder-ash-gray transition focus:ring-0 focus:outline-none focus:shadow-focus-ring {{ $errors->has('amount') ? 'border-danger focus:border-danger' : 'border-border-gray focus:border-primary' }}"
                    >
                </div>
                @error('amount')
                    <p class="mt-1.5 text-caption font-medium text-danger">{{ $message }}</p>
                @enderror
            </div>

            {{-- Informational Callout Box --}}
            <div class="rounded-card bg-fog-white border border-border-gray/80 p-3.5 text-left flex items-start gap-2.5">
                <x-dynamic-component component="lucide-info" class="w-4 h-4 text-primary shrink-0 mt-0.5" />
                <p class="text-[12px] text-slate-gray leading-relaxed">
                    Entri jurnal akrual otomatis dibentuk saat bill disimpan: <strong>Debit</strong> akun terpilih di atas, dan <strong>Kredit</strong> akun <em>201 - Utang Usaha (Accounts Payable)</em>[cite: 1].
                </p>
            </div>

            {{-- Buttons --}}
            <div class="flex items-center justify-end gap-3 pt-5 border-t border-border-gray/60">
                <x-button variant="secondary" size="md" href="{{ route('finance.vendor-bills.index') }}">
                    Batal
                </x-button>
                <x-button variant="primary" size="md" type="submit">
                    Simpan Tagihan Vendor
                </x-button>
            </div>
        </form>
    </div>
</x-app-layout>
