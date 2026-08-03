<?php

use App\Providers\AppServiceProvider;
use App\Modules\Identity\Providers\IdentityServiceProvider;
use App\Modules\Finance\Providers\FinanceServiceProvider;

return [
    AppServiceProvider::class,
    IdentityServiceProvider::class,
    FinanceServiceProvider::class,
];
