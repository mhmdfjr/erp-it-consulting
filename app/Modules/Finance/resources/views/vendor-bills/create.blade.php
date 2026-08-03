<x-app-layout>
    <x-slot name="header"><h1 class="text-heading font-medium text-ink-black">Buat Vendor Bill</h1></x-slot>

    <form method="POST" action="{{ route('finance.vendor-bills.store') }}" class="bg-paper-white border border-border-gray rounded-card shadow-subtle p-6 max-w-xl">
        @csrf

        <div class="mb-4">
            <label class="block text-label text-slate-gray mb-1">Vendor</label>
            <select name="vendor_id" class="w-full rounded-input border border-border-gray px-3 py-2 text-body">
                <option value="">-- pilih vendor --</option>
                @foreach ($vendors as $vendor)
                    <option value="{{ $vendor->id }}" @selected(old('vendor_id') == $vendor->id)>{{ $vendor->name }}</option>
                @endforeach
            </select>
            @error('vendor_id') <p class="text-caption text-danger mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-label text-slate-gray mb-1">Akun Beban/Aset (yang di-debit)</label>
            <select name="account_id" class="w-full rounded-input border border-border-gray px-3 py-2 text-body">
                <option value="">-- pilih akun --</option>
                @foreach ($accounts as $account)
                    <option value="{{ $account->id }}" @selected(old('account_id') == $account->id)>{{ $account->code }} - {{ $account->name }}</option>
                @endforeach
            </select>
            @error('account_id') <p class="text-caption text-danger mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-label text-slate-gray mb-1">Nomor Bill</label>
            <input type="text" name="bill_number" value="{{ old('bill_number') }}" class="w-full rounded-input border border-border-gray px-3 py-2 text-body">
            @error('bill_number') <p class="text-caption text-danger mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-label text-slate-gray mb-1">Tanggal Bill</label>
                <input type="date" name="bill_date" value="{{ old('bill_date') }}" class="w-full rounded-input border border-border-gray px-3 py-2 text-body">
                @error('bill_date') <p class="text-caption text-danger mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-label text-slate-gray mb-1">Jatuh Tempo</label>
                <input type="date" name="due_date" value="{{ old('due_date') }}" class="w-full rounded-input border border-border-gray px-3 py-2 text-body">
                @error('due_date') <p class="text-caption text-danger mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="mb-4">
            <label class="block text-label text-slate-gray mb-1">Jumlah</label>
            <input type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount') }}" class="w-full rounded-input border border-border-gray px-3 py-2 text-body tabular-nums">
            @error('amount') <p class="text-caption text-danger mt-1">{{ $message }}</p> @enderror
        </div>

        <p class="text-body-sm text-slate-gray mb-4">Journal entry accrual (debit akun di atas, kredit 201 Utang Usaha) akan digenerate otomatis saat bill disimpan.</p>

        <div class="flex justify-end gap-3">
            <x-button variant="secondary" href="{{ route('finance.vendor-bills.index') }}">Batal</x-button>
            <x-button variant="primary" type="submit">Simpan</x-button>
        </div>
    </form>
</x-app-layout>
