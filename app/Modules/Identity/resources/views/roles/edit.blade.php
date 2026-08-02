<x-app-layout>
    <x-slot name="header">Edit Role</x-slot>
    @include('identity::roles._form', ['permissions' => $permissions, 'role' => $role])
</x-app-layout>
