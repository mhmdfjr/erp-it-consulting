<?php

use App\Providers\AppServiceProvider;
use App\Modules\Identity\Providers\IdentityServiceProvider;
use App\Modules\Finance\Providers\FinanceServiceProvider;
use App\Modules\SalesInventory\Providers\SalesInventoryServiceProvider;
use App\Modules\HR\Providers\HRServiceProvider;

return [
    AppServiceProvider::class,
    IdentityServiceProvider::class,
    FinanceServiceProvider::class,
    SalesInventoryServiceProvider::class,
    HRServiceProvider::class,
];
