@php
    $navGroups = [
        'Utama' => [
            ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'layout-dashboard'],
        ],
        'Super Admin' => [
            ['label' => 'Pengguna', 'route' => 'identity.users.index', 'icon' => 'users'],
            ['label' => 'Role & Permission', 'route' => 'identity.roles.index', 'icon' => 'shield-check'],
            ['label' => 'Profil Perusahaan', 'route' => 'identity.company-profile.edit', 'icon' => 'building-2'],
            ['label' => 'Pengaturan Sistem', 'route' => 'identity.settings.index', 'icon' => 'settings'],
        ],
    ];
@endphp

<aside class="w-sidebar shrink-0 bg-fog-white border-r border-border-gray flex flex-col">
    <div class="h-topbar flex items-center px-4 border-b border-border-gray">
        <x-application-logo class="h-6 w-auto text-ink-black" />
        <span class="ml-2 text-body-lg font-medium">{{ config('app.name', 'ERP') }}</span>
    </div>

    <nav class="flex-1 overflow-y-auto py-4">
        @foreach ($navGroups as $groupLabel => $items)
            <div class="px-4 mb-1">
                <span class="text-caption text-ash-gray uppercase tracking-wide">{{ $groupLabel }}</span>
            </div>
            <ul class="mb-4">
                @foreach ($items as $item)
                    @php
                        $exists = Route::has($item['route']);
                        $isActive = $exists && request()->routeIs($item['route'] . '*');
                    @endphp
                    <li>
                        <a href="{{ $exists ? route($item['route']) : '#' }}"
                           class="flex items-center gap-3 px-4 py-2 text-body font-[450] border-l-[3px]
                                  {{ $isActive
                                        ? 'bg-mist-gray border-ink-black text-ink-black'
                                        : 'border-transparent text-slate-gray hover:bg-mist-gray hover:text-ink-black' }}">
                            <x-dynamic-component :component="'lucide-' . $item['icon']" class="w-4 h-4 shrink-0" />
                            {{ $item['label'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        @endforeach
    </nav>
</aside>
