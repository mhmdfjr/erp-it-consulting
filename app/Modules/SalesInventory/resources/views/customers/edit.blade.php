<x-app-layout>
    <x-slot name="header">
        Edit Pelanggan: {{ $customer->name }}
    </x-slot>

    <div class="max-w-3xl bg-paper-white border border-border-gray/80 rounded-card p-6 shadow-subtle"
         x-data="{ customerType: '{{ old('customer_type', $customer->customer_type) }}' }">
        <div class="border-b border-border-gray/60 pb-4 mb-6">
            <h3 class="text-heading-sm font-semibold text-ink-black">Perbarui Data Pelanggan</h3>
            <p class="text-caption text-slate-gray mt-0.5">Perbarui kontak dan informasi identitas pelanggan.</p>
        </div>

        <form method="POST" action="{{ route('sales.customers.update', $customer) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Nama Customer --}}
                <x-input
                    name="name"
                    label="Nama Pelanggan / Badan Usaha"
                    required
                    :value="old('name', $customer->name)"
                />

                {{-- Tipe Customer --}}
                <div>
                    <label for="customer_type" class="block text-label font-medium text-slate-gray mb-1.5">Klasifikasi Pelanggan <span class="text-danger">*</span></label>
                    <select
                        name="customer_type"
                        id="customer_type"
                        x-model="customerType"
                        class="w-full rounded-input border border-border-gray bg-paper-white px-3.5 py-2.5 text-body text-ink-black transition focus:ring-0 focus:outline-none focus:border-primary focus:shadow-focus-ring"
                    >
                        <option value="individual" @selected(old('customer_type', $customer->customer_type) === 'individual')>Individu / Perorangan</option>
                        <option value="corporate" @selected(old('customer_type', $customer->customer_type) === 'corporate')>Perusahaan / Korporat (B2B)</option>
                    </select>
                    @error('customer_type')
                        <p class="mt-1.5 text-caption font-medium text-danger">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Grid: Kontak Telepon & Email --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-input
                    name="phone"
                    label="No. Telepon / WhatsApp"
                    required
                    :value="old('phone', $customer->phone)"
                />

                <x-input
                    name="email"
                    type="email"
                    label="Alamat Email"
                    required
                    :value="old('email', $customer->email)"
                />
            </div>

            {{-- NPWP (Khusus Perusahaan / Korporat) --}}
            <div x-show="customerType === 'corporate'" x-transition>
                <x-input
                    name="npwp"
                    label="Nomor Pokok Wajib Pajak (NPWP)"
                    :value="old('npwp', $customer->npwp)"
                />
            </div>

            {{-- Alamat --}}
            <x-input
                name="address"
                label="Alamat Lengkap"
                required
                :value="old('address', $customer->address)"
            />

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3 pt-5 mt-6 border-t border-border-gray/60">
                <x-button variant="secondary" size="md" href="{{ route('sales.customers.index') }}">
                    Batal
                </x-button>
                <x-button variant="primary" size="md" type="submit">
                    Simpan Perubahan
                </x-button>
            </div>
        </form>
    </div>
</x-app-layout>
