<x-app-layout>
    <x-slot name="header">Tambah Role</x-slot>
    @include('identity::roles._form', ['permissions' => $permissions])
</x-app-layout>
