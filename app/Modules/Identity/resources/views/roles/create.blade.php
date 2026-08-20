<x-app-layout>
    <x-slot name="header">
        Tambah Peran & Hak Akses
    </x-slot>

    <div class="max-w-full mx-auto">
        @include('identity::roles._form', ['permissions' => $permissions])
    </div>
</x-app-layout>
