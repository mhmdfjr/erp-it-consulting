<x-app-layout>
    <x-slot name="header">Profil Perusahaan</x-slot>

    @if (session('success'))
        <div class="mb-4 rounded-input bg-success-bg text-success px-4 py-2 text-body-sm">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('identity.company-profile.update') }}" class="max-w-lg space-y-4">
        @csrf
        @method('PUT')

        <x-input name="name" label="Nama Perusahaan" :value="old('name', $profile->name)" required />
        <x-input name="npwp" label="NPWP" :value="old('npwp', $profile->npwp)" />
        <x-input name="phone" label="Telepon" :value="old('phone', $profile->phone)" />
        <x-input name="email" type="email" label="Email" :value="old('email', $profile->email)" />

        <div>
            <label class="block text-label text-slate-gray mb-1">Alamat</label>
            <textarea name="address" rows="3"
                class="w-full rounded-input border border-border-gray px-3 py-2 text-body focus:outline-none focus:border-ink-black focus:shadow-focus-ring">{{ old('address', $profile->address) }}</textarea>
        </div>

        <div class="flex justify-end">
            <x-button variant="primary" type="submit">Simpan</x-button>
        </div>
    </form>
</x-app-layout>
