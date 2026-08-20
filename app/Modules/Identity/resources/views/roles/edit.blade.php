<x-app-layout>
    <x-slot name="header">
        Edit Peran: {{ $role->name }}
    </x-slot>

    <div class="max-w-full mx-auto">
        @include('identity::roles._form', ['permissions' => $permissions, 'role' => $role])
    </div>
</x-app-layout>
