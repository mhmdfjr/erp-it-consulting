<x-app-layout>
    <x-slot name="header">
        Tambah Pengguna Baru
    </x-slot>

    <div class="max-w-full mx-auto">
        @include('identity::users._form', ['roles' => $roles])
    </div>
</x-app-layout>
