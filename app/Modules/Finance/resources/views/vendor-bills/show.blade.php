<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-heading font-medium text-ink-black">{{ $vendorBill->bill_number }}</h1>
                <p class="text-body-sm text-slate-gray">{{ $vendorBill->vendor->name }}</p>
            </div>
            <x-badge :status="$vendorBill->status === 'paid' ? 'success' : ($vendorBill->status === 'void' ? 'danger' : 'warning')">
                {{ ucfirst($vendorBill->status) }}
            </x-badge>
        </div>
    </x-slot>

    @if (session('success')) <div class="mb-4 rounded-input border-l-4 border-success bg-success-bg text-success px-4 py-3 text-body-sm">{{ session('success') }}</div> @endif

    <div class="bg-paper-white border border-border-gray rounded-card shadow-subtle p-6 mb-6">
        <dl class="grid grid-cols-2 gap-4 text-body">
            <div><dt class="text-label text-slate-gray">Akun</dt><dd>{{ $vendorBill->account->code }} - {{ $vendorBill->account->name }}</dd></div>
            <div><dt class="text-label text-slate-gray">Jumlah</dt><dd class="tabular-nums">{{ number_format($vendorBill->amount, 2) }}</dd></div>
            <div><dt class="text-label text-slate-gray">Tanggal Bill</dt><dd>{{ $vendorBill->bill_date->format('d M Y') }}</dd></div>
            <div><dt class="text-label text-slate-gray">Jatuh Tempo</dt><dd>{{ $vendorBill->due_date->format('d M Y') }}</dd></div>
        </dl>
    </div>

    @can('finance.vendorbill.pay')
        @if ($vendorBill->status === 'unpaid')
            <div class="bg-paper-white border border-border-gray rounded-card shadow-subtle p-6 max-w-md">
                <h2 class="text-heading-sm font-medium text-ink-black mb-4">Tandai Lunas</h2>
                <form method="POST" action="{{ route('finance.vendor-bills.mark-as-paid', $vendorBill) }}">
                    @csrf
                    <label class="block text-label text-slate-gray mb-1">Dibayar dari akun</label>
                    <select name="payment_account_id" class="w-full rounded-input border border-border-gray px-3 py-2 text-body mb-4">
                        <option value="">-- pilih akun kas/bank --</option>
                        @foreach ($cashAndBankAccounts as $account)
                            <option value="{{ $account->id }}">{{ $account->code }} - {{ $account->name }}</option>
                        @endforeach
                    </select>
                    @error('payment_account_id') <p class="text-caption text-danger mt-1 mb-2">{{ $message }}</p> @enderror

                    <x-button variant="primary" type="submit">
                        Konfirmasi Pelunasan
                    </x-button>
                </form>
            </div>
        @endif
    @endcan
</x-app-layout>
