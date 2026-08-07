@php
    $isEdit = isset($role) && $role->exists;
    $selectedPermissions = collect(old('permissions', $isEdit ? $role->permissions->pluck('name')->toArray() : []));
    $isSuperAdmin = $isEdit && $role->name === 'Super Admin';
@endphp

<form method="POST" action="{{ $isEdit ? route('identity.roles.update', $role) : route('identity.roles.store') }}" class="max-w-2xl space-y-4">
    @csrf
    @if ($isEdit) @method('PUT') @endif

    <x-input name="name" label="Nama Role" :value="old('name', $role->name ?? '')" required :disabled="$isSuperAdmin" />

    @if ($isSuperAdmin)
        <p class="text-body-sm text-warning">Role Super Admin tidak bisa diubah namanya atau permission-nya.</p>
    @endif

    <div>
        <label class="block text-label text-slate-gray mb-2">Permission</label>
        <div class="grid grid-cols-2 gap-4">
            @foreach ($permissions as $group => $items)
                <div>
                    <span class="text-caption text-ash-gray uppercase">{{ $group }}</span>
                    <div class="space-y-1 mt-1">
                        @foreach ($items as $permission)
                            <label class="flex items-center gap-2 text-body-sm">
                                <input type="checkbox" name="permissions[]" value="{{ $permission->name }}"
                                    class="rounded border-border-gray text-ink-black focus:ring-0 focus:shadow-focus-ring"
                                    @checked($selectedPermissions->contains($permission->name))
                                    @disabled($isSuperAdmin)>
                                {{ $permission->name }}
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="flex justify-end gap-2">
        <x-button variant="danger" href="{{ route('identity.roles.index') }}">Batal</x-button>
        @unless ($isSuperAdmin)
            <x-button variant="primary" type="submit">{{ $isEdit ? 'Simpan' : 'Buat Role' }}</x-button>
        @endunless
    </div>
</form>
