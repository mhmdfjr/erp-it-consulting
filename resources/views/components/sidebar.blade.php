@php
   $navGroups = [
        'Main' => [
            ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'layout-dashboard'],
        ],

        'Super Admin' => [
            ['label' => 'User', 'route' => 'identity.users.index', 'icon' => 'users'],
            ['label' => 'Role & Permission', 'route' => 'identity.roles.index', 'icon' => 'shield-check'],
            ['label' => 'Company Profile', 'route' => 'identity.company-profile.edit', 'icon' => 'building-2'],
            ['label' => 'System Setting', 'route' => 'identity.settings.index', 'icon' => 'settings'],
        ],

        'Finance' => [
            ['label' => 'Chart of Accounts', 'route' => 'finance.coa.index', 'icon' => 'landmark'],
            ['label' => 'Journal Entry', 'route' => 'finance.journal-entries.index', 'icon' => 'book-open-text'],
            ['label' => 'Vendor', 'route' => 'finance.vendors.index', 'icon' => 'truck'],
            ['label' => 'Vendor Bill', 'route' => 'finance.vendor-bills.index', 'icon' => 'receipt'],
            ['label' => 'Invoice', 'route' => 'finance.invoices.index', 'icon' => 'file-text'],
        ],

        'Sales' => [
            ['label' => 'Product & Service', 'route' => 'sales.items.index', 'icon' => 'package'],
            ['label' => 'Customer', 'route' => 'sales.customers.index', 'icon' => 'contact-round'],
            ['label' => 'Sales Order', 'route' => 'sales.orders.index', 'icon' => 'shopping-cart'],
        ],

        'HR & Payroll' => [
            ['label' => 'Department', 'route' => 'hr.departments.index', 'icon' => 'blocks'],
            ['label' => 'Position', 'route' => 'hr.positions.index', 'icon' => 'briefcase'],
            ['label' => 'Employee', 'route' => 'hr.employees.index', 'icon' => 'user-shield'],
            ['label' => 'Attendance', 'route' => 'hr.attendances.index', 'icon' => 'calendar-check'],
            ['label' => 'Payroll Component', 'route' => 'hr.payroll-components.index', 'icon' => 'credit-card'],
            ['label' => 'Payroll Process', 'route' => 'hr.payroll-runs.index', 'icon' => 'circle-dollar-sign'],
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
