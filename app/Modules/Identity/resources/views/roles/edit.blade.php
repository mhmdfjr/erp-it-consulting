<x-app-layout>
    <x-slot name="header">Edit Peran & Perizinan</x-slot>
    @include('identity::roles._form', ['permissions' => $permissions, 'role' => $role])
</x-app-layout>
