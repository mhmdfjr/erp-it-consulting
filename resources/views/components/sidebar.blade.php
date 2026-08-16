@php
    $navGroups = [
        'Main' => [
            ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'layout-dashboard', 'permission' => null],
        ],

        'Super Admin' => [
            ['label' => 'User', 'route' => 'identity.users.index', 'icon' => 'users', 'permission' => 'identity.user.view'],
            ['label' => 'Role & Permission', 'route' => 'identity.roles.index', 'icon' => 'shield-check', 'permission' => 'identity.role.view'],
            ['label' => 'Company Profile', 'route' => 'identity.company-profile.edit', 'icon' => 'building-2', 'permission' => 'identity.settings.manage'],
            ['label' => 'System Setting', 'route' => 'identity.settings.index', 'icon' => 'settings', 'permission' => 'identity.settings.manage'],
        ],

        'Finance' => [
            ['label' => 'Chart of Accounts', 'route' => 'finance.coa.index', 'icon' => 'landmark', 'permission' => 'finance.coa.view'],
            ['label' => 'Journal Entry', 'route' => 'finance.journal-entries.index', 'icon' => 'book-open-text', 'permission' => 'finance.journal.view'],
            ['label' => 'Vendor', 'route' => 'finance.vendors.index', 'icon' => 'truck', 'permission' => 'finance.vendor.view'],
            ['label' => 'Vendor Bill', 'route' => 'finance.vendor-bills.index', 'icon' => 'receipt', 'permission' => 'finance.vendorbill.view'],
            ['label' => 'Invoice', 'route' => 'finance.invoices.index', 'icon' => 'file-text', 'permission' => 'finance.invoice.view'],
            ['label' => 'Income Report', 'route' => 'finance.reports.income-statement', 'icon' => 'trending-up', 'permission' => 'finance.report.view'],
            ['label' => 'Balance Report', 'route' => 'finance.reports.balance-sheet', 'icon' => 'scale', 'permission' => 'finance.report.view'],
        ],

        'Sales' => [
            ['label' => 'Product & Service', 'route' => 'sales.items.index', 'icon' => 'package', 'permission' => 'sales.item.view'],
            ['label' => 'Customer', 'route' => 'sales.customers.index', 'icon' => 'contact-round', 'permission' => 'sales.customer.view'],
            ['label' => 'Sales Order', 'route' => 'sales.orders.index', 'icon' => 'shopping-cart', 'permission' => 'sales.order.view'],
        ],

        'HR & Payroll' => [
            ['label' => 'Department', 'route' => 'hr.departments.index', 'icon' => 'blocks', 'permission' => 'hr.department.manage'],
            ['label' => 'Position', 'route' => 'hr.positions.index', 'icon' => 'briefcase', 'permission' => 'hr.position.manage'],
            ['label' => 'Employee', 'route' => 'hr.employees.index', 'icon' => 'user-shield', 'permission' => 'hr.employee.view'],
            ['label' => 'Attendance', 'route' => 'hr.attendances.index', 'icon' => 'calendar-check', 'permission' => 'hr.attendance.view'],
            ['label' => 'Payroll Component', 'route' => 'hr.payroll-components.index', 'icon' => 'credit-card', 'permission' => 'hr.payrollcomponent.view'],
            ['label' => 'Payroll Process', 'route' => 'hr.payroll-runs.index', 'icon' => 'circle-dollar-sign', 'permission' => 'hr.payroll.view'],
        ],
    ];

    $user = auth()->user();

    // Filter tiap group: item tanpa 'permission' (null) selalu tampil untuk
    // user yang sudah login (misal Dashboard). Item dengan permission cuma
    // tampil kalau user->can() true -- satu jalur authorization yang sama
    // dengan yang dipakai Controller, bukan hasRole('Super Admin') hardcode.
    // Group yang hasil filter-nya kosong (tidak ada item visible) di-skip
    // seluruhnya, supaya tidak ada header grup kosong tanpa isi.
    $visibleNavGroups = collect($navGroups)
        ->map(function ($items) use ($user) {
            return collect($items)->filter(function ($item) use ($user) {
                return is_null($item['permission']) || $user->can($item['permission']);
            });
        })
        ->filter(fn ($items) => $items->isNotEmpty());
@endphp

<aside class="w-sidebar shrink-0 bg-fog-white border-r border-border-gray flex flex-col">
    <div class="h-topbar flex items-center px-4 border-b border-border-gray">
        <x-application-logo class="h-6 w-auto text-ink-black" />
        <span class="ml-2 text-body-lg font-medium">{{ config('app.name', 'ERP') }}</span>
    </div>

    <nav class="flex-1 overflow-y-auto py-4">
        @foreach ($visibleNavGroups as $groupLabel => $items)
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
