<x-app-layout>
    <x-slot name="header">Tambah Peran & Perizinan</x-slot>
    @include('identity::roles._form', ['permissions' => $permissions])
</x-app-layout>
