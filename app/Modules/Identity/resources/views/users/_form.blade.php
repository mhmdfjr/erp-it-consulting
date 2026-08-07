@php
    $isEdit = isset($user) && $user->exists;
    $selectedRoles = collect(old('roles', $isEdit ? $user->roles->pluck('name')->toArray() : []));
@endphp

<form method="POST" action="{{ $isEdit ? route('identity.users.update', $user) : route('identity.users.store') }}" class="max-w-lg space-y-4">
    @csrf
    @if ($isEdit) @method('PUT') @endif

    <x-input name="name" label="Nama" :value="old('name', $user->name ?? '')" required />
    <x-input name="email" type="email" label="Email" :value="old('email', $user->email ?? '')" required />
    <x-input name="password" type="password" :label="$isEdit ? 'Password (kosongkan jika tidak diubah)' : 'Password'" :required="! $isEdit" />

    <div>
        <label class="block text-label text-slate-gray mb-1">Role</label>
        <div class="space-y-1">
            @foreach ($roles as $role)
                <label class="flex items-center gap-2 text-body-sm">
                    <input type="checkbox" name="roles[]" value="{{ $role->name }}"
                        class="rounded border-border-gray text-ink-black focus:ring-0 focus:shadow-focus-ring"
                        @checked($selectedRoles->contains($role->name))>
                    {{ $role->name }}
                </label>
            @endforeach
        </div>
    </div>

    <div>
        <label class="block text-label text-slate-gray mb-1">Status</label>
        <input type="checkbox" name="is_active" value="1" id="is_active"
            class="rounded border-border-gray text-ink-black focus:ring-0 focus:shadow-focus-ring"
            @checked(old('is_active', $user->is_active ?? true))>
        <label for="is_active" class="text-body-sm">Aktif</label>
    </div>

    <div class="flex justify-end gap-2">
        <x-button variant="danger" href="{{ route('identity.users.index') }}">Batal</x-button>
        <x-button variant="primary" type="submit">{{ $isEdit ? 'Simpan' : 'Buat Pengguna' }}</x-button>
    </div>
</form>
