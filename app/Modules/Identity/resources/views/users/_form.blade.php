@php
    $isEdit = isset($user) && $user->exists;
    $selectedRoles = collect(old('roles', $isEdit ? $user->roles->pluck('name')->toArray() : []));
@endphp

<div class="bg-paper-white border border-border-gray/80 rounded-card p-6 shadow-subtle max-w-2xl">
    <div class="border-b border-border-gray/60 pb-4 mb-6">
        <h3 class="text-heading-sm font-semibold text-ink-black">
            {{ $isEdit ? 'Perbarui Informasi Pengguna' : 'Informasi Pengguna Baru' }}
        </h3>
        <p class="text-caption text-slate-gray mt-0.5">
            Lengkapi data akun dan pilih perizinan role yang sesuai di bawah ini.
        </p>
    </div>

    <form method="POST" action="{{ $isEdit ? route('identity.users.update', $user) : route('identity.users.store') }}" class="space-y-5">
        @csrf
        @if ($isEdit) @method('PUT') @endif

        {{-- Nama --}}
        <x-input
            name="name"
            label="Nama Lengkap"
            :value="old('name', $user->name ?? '')"
            placeholder="Contoh: Budi Santoso"
            required
        />

        {{-- Email --}}
        <x-input
            name="email"
            type="email"
            label="Alamat Email"
            :value="old('email', $user->email ?? '')"
            placeholder="nama@perusahaan.com"
            required
        />

        {{-- Password --}}
        <div>
            <x-input
                name="password"
                type="password"
                :label="$isEdit ? 'Password (Kosongkan jika tidak ingin diubah)' : 'Password Akun'"
                placeholder="Minimal 8 karakter"
                :required="! $isEdit"
            />
            @if($isEdit)
                <p class="text-caption text-ash-gray mt-1">Biarkan kosong jika password saat ini tetap digunakan.</p>
            @endif
        </div>

        {{-- Role Selection --}}
        <div class="pt-2">
            <label class="block text-label font-medium text-slate-gray mb-2">Penugasan Role / Hak Akses</label>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-4 rounded-input bg-fog-white border border-border-gray/60">
                @foreach ($roles as $role)
                    <label class="flex items-center gap-2.5 text-body-sm font-medium text-ink-black cursor-pointer select-none">
                        <input
                            type="checkbox"
                            name="roles[]"
                            value="{{ $role->name }}"
                            class="w-4 h-4 rounded border-border-gray text-primary focus:ring-0 focus:shadow-focus-ring"
                            @checked($selectedRoles->contains($role->name))
                        >
                        <span>{{ $role->name }}</span>
                    </label>
                @endforeach
            </div>
            @error('roles')
                <p class="mt-1.5 text-caption font-medium text-danger">{{ $message }}</p>
            @enderror
        </div>

        {{-- Status Toggle --}}
        <div class="pt-2">
            <label class="block text-label font-medium text-slate-gray mb-1.5">Status Akun</label>
            <label for="is_active" class="inline-flex items-center gap-2.5 cursor-pointer select-none">
                <input
                    type="checkbox"
                    name="is_active"
                    value="1"
                    id="is_active"
                    class="w-4 h-4 rounded border-border-gray text-primary focus:ring-0 focus:shadow-focus-ring"
                    @checked(old('is_active', $user->is_active ?? true))
                >
                <span class="text-body-sm font-medium text-ink-black">Akun Aktif (Dapat Login ke Sistem)</span>
            </label>
        </div>

        {{-- Footer Actions --}}
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-border-gray/60">
            <x-button variant="secondary" href="{{ route('identity.users.index') }}">
                Batal
            </x-button>
            <x-button variant="primary" type="submit">
                {{ $isEdit ? 'Simpan Perubahan' : 'Buat Pengguna' }}
            </x-button>
        </div>
    </form>
</div>
