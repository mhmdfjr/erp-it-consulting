@php $vendor = $vendor ?? null; @endphp

<div class="space-y-5">
    {{-- Nama Vendor --}}
    <div>
        <label for="name" class="block text-label font-medium text-slate-gray mb-1.5">Nama Vendor / Perusahaan <span class="text-danger">*</span></label>
        <input
            type="text"
            name="name"
            id="name"
            placeholder="Contoh: PT Sumber Logistik Utama"
            value="{{ old('name', $vendor?->name) }}"
            required
            class="w-full rounded-input border bg-paper-white px-3.5 py-2.5 text-body text-ink-black placeholder-ash-gray transition-colors focus:ring-0 focus:outline-none focus:shadow-focus-ring {{ $errors->has('name') ? 'border-danger focus:border-danger' : 'border-border-gray focus:border-primary' }}"
        >
        @error('name')
            <p class="mt-1.5 text-caption font-medium text-danger">{{ $message }}</p>
        @enderror
    </div>

    {{-- NPWP --}}
    <div>
        <label for="npwp" class="block text-label font-medium text-slate-gray mb-1.5">Nomor NPWP</label>
        <input
            type="text"
            name="npwp"
            id="npwp"
            placeholder="Contoh: 01.234.567.8-901.000"
            value="{{ old('npwp', $vendor?->npwp) }}"
            class="w-full rounded-input border bg-paper-white px-3.5 py-2.5 text-body text-ink-black placeholder-ash-gray transition-colors focus:ring-0 focus:outline-none focus:shadow-focus-ring {{ $errors->has('npwp') ? 'border-danger focus:border-danger' : 'border-border-gray focus:border-primary' }}"
        >
        @error('npwp')
            <p class="mt-1.5 text-caption font-medium text-danger">{{ $message }}</p>
        @enderror
    </div>

    {{-- Grid: Kontak Telepon & Email --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label for="phone" class="block text-label font-medium text-slate-gray mb-1.5">No. Telepon / WhatsApp</label>
            <input
                type="text"
                name="phone"
                id="phone"
                placeholder="Contoh: 081234567890"
                value="{{ old('phone', $vendor?->phone) }}"
                class="w-full rounded-input border bg-paper-white px-3.5 py-2.5 text-body text-ink-black placeholder-ash-gray transition-colors focus:ring-0 focus:outline-none focus:shadow-focus-ring {{ $errors->has('phone') ? 'border-danger focus:border-danger' : 'border-border-gray focus:border-primary' }}"
            >
            @error('phone')
                <p class="mt-1.5 text-caption font-medium text-danger">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="email" class="block text-label font-medium text-slate-gray mb-1.5">Alamat Email</label>
            <input
                type="email"
                name="email"
                id="email"
                placeholder="vendor@domain.com"
                value="{{ old('email', $vendor?->email) }}"
                class="w-full rounded-input border bg-paper-white px-3.5 py-2.5 text-body text-ink-black placeholder-ash-gray transition-colors focus:ring-0 focus:outline-none focus:shadow-focus-ring {{ $errors->has('email') ? 'border-danger focus:border-danger' : 'border-border-gray focus:border-primary' }}"
            >
            @error('email')
                <p class="mt-1.5 text-caption font-medium text-danger">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- Alamat --}}
    <div>
        <label for="address" class="block text-label font-medium text-slate-gray mb-1.5">Alamat Kantor / Operasional</label>
        <textarea
            name="address"
            id="address"
            rows="3"
            placeholder="Tuliskan alamat lengkap vendor"
            class="w-full rounded-input border bg-paper-white px-3.5 py-2.5 text-body text-ink-black placeholder-ash-gray transition-colors focus:ring-0 focus:outline-none focus:shadow-focus-ring {{ $errors->has('address') ? 'border-danger focus:border-danger' : 'border-border-gray focus:border-primary' }}"
        >{{ old('address', $vendor?->address) }}</textarea>
        @error('address')
            <p class="mt-1.5 text-caption font-medium text-danger">{{ $message }}</p>
        @enderror
    </div>
</div>
