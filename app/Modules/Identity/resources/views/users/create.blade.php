<x-app-layout>
    <x-slot name="header">Tambah Pengguna</x-slot>
    @include('identity::users._form', ['roles' => $roles])
</x-app-layout>
