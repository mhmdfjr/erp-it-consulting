<x-app-layout>
    <x-slot name="header">Tambah Pelanggan</x-slot>

    <form method="POST" action="{{ route('sales.customers.store') }}"
        x-data="{ customerType: '{{ old('customer_type', 'individual') }}' }" class="max-w-xl space-y-4">
        @csrf

        <x-input name="name" label="Nama Customer" required :value="old('name') ?? ''" />

        <div>
            <label class="text-label text-slate-gray block mb-1">Tipe Customer</label>
            <select name="customer_type" x-model="customerType" class="w-full rounded-input border-border-gray">
                <option value="individual" {{ old('customer_type', 'individual') === 'individual' ? 'selected' : '' }}>Individu</option>
                <option value="corporate" {{ old('customer_type') === 'corporate' ? 'selected' : '' }}>Perusahaan</option>
            </select>
            @error('customer_type') <p class="text-caption text-danger mt-1">{{ $message }}</p> @enderror
        </div>

        <x-input name="address" label="Alamat" required :value="old('address') ?? ''" />
        <x-input name="phone" label="No. Telepon" required :value="old('phone') ?? ''" />
        <x-input name="email" type="email" label="Email" required :value="old('email') ?? ''" />

        <div x-show="customerType === 'corporate'">
            <x-input name="npwp" label="NPWP" :value="old('npwp') ?? ''" />
        </div>

        <div class="flex gap-2">
            <x-button type="submit" variant="primary">Simpan</x-button>
            <x-button variant="danger" href="{{ route('sales.customers.index') }}">Batal</x-button>
        </div>
    </form>
</x-app-layout>
