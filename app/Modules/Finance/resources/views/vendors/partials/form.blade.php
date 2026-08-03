@php $vendor = $vendor ?? null; @endphp

<div class="mb-4">
    <label class="block text-label text-slate-gray mb-1">Nama Vendor</label>
    <input type="text" name="name" value="{{ old('name', $vendor?->name) }}" class="w-full rounded-input border border-border-gray px-3 py-2 text-body focus:border-ink-black focus:shadow-focus-ring outline-none">
    @error('name') <p class="text-caption text-danger mt-1">{{ $message }}</p> @enderror
</div>

<div class="mb-4">
    <label class="block text-label text-slate-gray mb-1">NPWP</label>
    <input type="text" name="npwp" value="{{ old('npwp', $vendor?->npwp) }}" class="w-full rounded-input border border-border-gray px-3 py-2 text-body focus:border-ink-black focus:shadow-focus-ring outline-none">
    @error('npwp') <p class="text-caption text-danger mt-1">{{ $message }}</p> @enderror
</div>

<div class="mb-4">
    <label class="block text-label text-slate-gray mb-1">Alamat</label>
    <textarea name="address" rows="2" class="w-full rounded-input border border-border-gray px-3 py-2 text-body focus:border-ink-black focus:shadow-focus-ring outline-none">{{ old('address', $vendor?->address) }}</textarea>
    @error('address') <p class="text-caption text-danger mt-1">{{ $message }}</p> @enderror
</div>

<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="block text-label text-slate-gray mb-1">Telepon</label>
        <input type="text" name="phone" value="{{ old('phone', $vendor?->phone) }}" class="w-full rounded-input border border-border-gray px-3 py-2 text-body focus:border-ink-black focus:shadow-focus-ring outline-none">
        @error('phone') <p class="text-caption text-danger mt-1">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="block text-label text-slate-gray mb-1">Email</label>
        <input type="email" name="email" value="{{ old('email', $vendor?->email) }}" class="w-full rounded-input border border-border-gray px-3 py-2 text-body focus:border-ink-black focus:shadow-focus-ring outline-none">
        @error('email') <p class="text-caption text-danger mt-1">{{ $message }}</p> @enderror
    </div>
</div>
