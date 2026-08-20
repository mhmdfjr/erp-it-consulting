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

    $visibleNavGroups = collect($navGroups)
        ->map(function ($items) use ($user) {
            return collect($items)->filter(function ($item) use ($user) {
                return is_null($item['permission']) || $user->can($item['permission']);
            });
        })
        ->filter(fn ($items) => $items->isNotEmpty());
@endphp

<aside class="w-sidebar shrink-0 bg-fog-white border-r border-border-gray flex flex-col justify-between h-screen sticky top-0">
    <div>
        <!-- App Logo & Branding -->
        <div class="h-topbar flex items-center px-6 border-b border-border-gray/70">
            <div class="h-8 w-8 rounded-card bg-primary text-paper-white flex items-center justify-center shadow-subtle mr-2.5">
                <x-dynamic-component component="lucide-layers" class="w-4 h-4 text-paper-white" />
            </div>
            <span class="text-body font-bold tracking-tight text-ink-black">{{ config('app.name', 'ERP') }}</span>
        </div>

        <!-- Navigation Links -->
        <nav class="overflow-y-auto px-3.5 py-4 max-h-[calc(100vh-210px)] space-y-6">
            @foreach ($visibleNavGroups as $groupLabel => $items)
                <div>
                    <div class="px-3 mb-2">
                        <span class="text-[11px] font-bold text-ash-gray uppercase tracking-wider">{{ $groupLabel }}</span>
                    </div>
                    <ul class="space-y-1">
                        @foreach ($items as $item)
                            @php
                                $exists = Route::has($item['route']);
                                $isActive = $exists && request()->routeIs($item['route'] . '*');
                            @endphp
                            <li>
                                <a href="{{ $exists ? route($item['route']) : '#' }}"
                                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-card text-body-sm font-medium transition-all duration-200
                                          {{ $isActive
                                                ? 'bg-paper-white text-ink-black shadow-subtle'
                                                : 'text-slate-gray hover:bg-mist-gray/60 hover:text-ink-black' }}">

                                    <!-- Icon Container -->
                                    <span class="flex items-center justify-center w-7 h-7 rounded-input transition-colors
                                          {{ $isActive
                                                ? 'bg-primary text-paper-white shadow-sm'
                                                : 'bg-paper-white text-primary shadow-subtle' }}">
                                        <x-dynamic-component :component="'lucide-' . $item['icon']" class="w-4 h-4" />
                                    </span>

                                    <span class="truncate">{{ $item['label'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </nav>
    </div>

    <!-- Help / Documentation Card Widget -->
    <div class="p-3.5 border-t border-border-gray/60">
        <div class="rounded-card bg-gradient-to-br from-primary via-primary-hover to-secondary p-4 text-paper-white shadow-subtle relative overflow-hidden">
            <div class="relative z-10">
                <div class="w-7 h-7 rounded-full bg-paper-white/20 flex items-center justify-center mb-2.5">
                    <x-dynamic-component component="lucide-help-circle" class="w-4 h-4 text-paper-white" />
                </div>
                <h4 class="text-body-sm font-semibold leading-snug">Need help?</h4>
                <p class="text-caption text-paper-white/80 mt-0.5 mb-3">Please check our docs</p>
                <a href="#" class="block text-center w-full py-1.5 px-3 bg-paper-white text-ink-black rounded-input text-caption font-bold tracking-wider uppercase hover:bg-mist-gray transition">
                    Documentation
                </a>
            </div>
            <!-- Background Radial Glow -->
            <div class="absolute -bottom-8 -right-8 w-24 h-24 bg-accent-pink/30 rounded-full blur-xl pointer-events-none"></div>
        </div>
    </div>
</aside>
