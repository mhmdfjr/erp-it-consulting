<x-app-layout>
    <x-slot name="header">
        Tambah Pelanggan Baru
    </x-slot>

    <div class="max-w-3xl bg-paper-white border border-border-gray/80 rounded-card p-6 shadow-subtle"
         x-data="{ customerType: '{{ old('customer_type', 'individual') }}' }">
        <div class="border-b border-border-gray/60 pb-4 mb-6">
            <h3 class="text-heading-sm font-semibold text-ink-black">Informasi Pelanggan</h3>
            <p class="text-caption text-slate-gray mt-0.5">Daftarkan data kontak dan identitas legal pelanggan perorangan atau korporat.</p>
        </div>

        <form method="POST" action="{{ route('sales.customers.store') }}" class="space-y-5">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Nama Customer --}}
                <x-input
                    name="name"
                    label="Nama Pelanggan / Badan Usaha"
                    placeholder="Contoh: PT Surya Gemilang atau Budi Santoso"
                    required
                    :value="old('name') ?? ''"
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
                        <option value="individual">Individu / Perorangan</option>
                        <option value="corporate">Perusahaan / Korporat (B2B)</option>
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
                    placeholder="Contoh: 08123456789"
                    required
                    :value="old('phone') ?? ''"
                />

                <x-input
                    name="email"
                    type="email"
                    label="Alamat Email"
                    placeholder="kontak@pelanggan.com"
                    required
                    :value="old('email') ?? ''"
                />
            </div>

            {{-- NPWP (Khusus Perusahaan / Korporat) --}}
            <div x-show="customerType === 'corporate'" x-transition>
                <x-input
                    name="npwp"
                    label="Nomor Pokok Wajib Pajak (NPWP)"
                    placeholder="Contoh: 01.234.567.8-901.000"
                    :value="old('npwp') ?? ''"
                />
            </div>

            {{-- Alamat --}}
            <x-input
                name="address"
                label="Alamat Lengkap"
                placeholder="Tuliskan nama jalan, gedung, kota, dan kode pos"
                required
                :value="old('address') ?? ''"
            />

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3 pt-5 mt-6 border-t border-border-gray/60">
                <x-button variant="secondary" size="md" href="{{ route('sales.customers.index') }}">
                    Batal
                </x-button>
                <x-button variant="primary" size="md" type="submit">
                    Simpan Pelanggan
                </x-button>
            </div>
        </form>
    </div>
</x-app-layout>
