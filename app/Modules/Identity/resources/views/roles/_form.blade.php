@php
    $isEdit = isset($role) && $role->exists;
    $selectedPermissions = collect(old('permissions', $isEdit ? $role->permissions->pluck('name')->toArray() : []));
    $isSuperAdmin = $isEdit && $role->name === 'Super Admin';
@endphp

<div class="bg-paper-white border border-border-gray/80 rounded-card p-6 shadow-subtle max-w-4xl">
    <div class="border-b border-border-gray/60 pb-4 mb-6">
        <h3 class="text-heading-sm font-semibold text-ink-black">
            {{ $isEdit ? 'Perbarui Peran & Hak Akses' : 'Buat Peran Baru' }}
        </h3>
        <p class="text-caption text-slate-gray mt-0.5">
            Tentukan nama peran dan centang modul otorisasi yang dapat diakses oleh role ini.
        </p>
    </div>

    @if ($isSuperAdmin)
        <div class="mb-6 rounded-input bg-accent-peach-tint border border-accent-peach/30 text-[#9c3f15] px-4 py-3 text-body-sm flex items-center gap-2">
            <x-dynamic-component component="lucide-shield-alert" class="w-4 h-4 shrink-0" />
            <span>Role <strong>Super Admin</strong> adalah peran sistem default dan memiliki seluruh hak akses secara permanen.</span>
        </div>
    @endif

    <form method="POST" action="{{ $isEdit ? route('identity.roles.update', $role) : route('identity.roles.store') }}" class="space-y-6">
        @csrf
        @if ($isEdit) @method('PUT') @endif

        {{-- Role Name --}}
        <div class="max-w-md">
            <x-input
                name="name"
                label="Nama Role / Peran"
                :value="old('name', $role->name ?? '')"
                placeholder="Contoh: Inventory Staff"
                required
                :disabled="$isSuperAdmin"
            />
        </div>

        {{-- Grouped Permissions Matrix --}}
        <div class="pt-2">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <label class="block text-label font-bold text-ink-black">Daftar Hak Akses (Permissions)</label>
                    <p class="text-caption text-slate-gray">Pilih modul fungsional yang diizinkan untuk peran ini.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach ($permissions as $group => $items)
                    <div class="border border-border-gray/80 rounded-card p-4 bg-fog-white/60">
                        <div class="flex items-center justify-between pb-2 mb-3 border-b border-border-gray/60">
                            <span class="text-[11px] font-bold text-primary uppercase tracking-wider flex items-center gap-1.5">
                                <x-dynamic-component component="lucide-folder-key" class="w-3.5 h-3.5" />
                                {{ $group }}
                            </span>
                            <span class="text-[11px] font-semibold text-slate-gray tabular-nums">
                                {{ count($items) }} izin
                            </span>
                        </div>

                        <div class="space-y-2">
                            @foreach ($items as $permission)
                                <label class="flex items-center gap-2.5 text-body-sm font-medium text-ink-black cursor-pointer select-none hover:text-primary transition">
                                    <input
                                        type="checkbox"
                                        name="permissions[]"
                                        value="{{ $permission->name }}"
                                        class="w-4 h-4 rounded border-border-gray text-primary focus:ring-0 focus:shadow-focus-ring disabled:opacity-50"
                                        @checked($selectedPermissions->contains($permission->name))
                                        @disabled($isSuperAdmin)
                                    >
                                    <span class="text-caption font-medium">{{ $permission->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
            @error('permissions')
                <p class="mt-2 text-caption font-medium text-danger">{{ $message }}</p>
            @enderror
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-end gap-3 pt-5 border-t border-border-gray/60">
            <x-button variant="secondary" href="{{ route('identity.roles.index') }}">
                Batal
            </x-button>
            @unless ($isSuperAdmin)
                <x-button variant="primary" type="submit">
                    {{ $isEdit ? 'Simpan Perubahan' : 'Buat Peran Baru' }}
                </x-button>
            @endunless
        </div>
    </form>
</div>
