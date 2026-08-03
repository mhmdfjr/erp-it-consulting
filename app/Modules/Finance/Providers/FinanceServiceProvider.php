<?php

namespace App\Modules\Finance\Providers;

use App\Modules\Finance\Listeners\CreateJournalEntryFromPayroll;
use App\Modules\Finance\Listeners\CreateJournalEntryFromSalesOrder;
use App\Modules\Finance\Livewire\CreateJournalEntry;
use App\Modules\HR\Events\PayrollProcessed;
use App\Modules\SalesInventory\Events\SalesOrderCompleted;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class FinanceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'finance');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        Livewire::component('finance.create-journal-entry', CreateJournalEntry::class);

        Event::listen(SalesOrderCompleted::class, CreateJournalEntryFromSalesOrder::class);
        Event::listen(PayrollProcessed::class, CreateJournalEntryFromPayroll::class);

    }
}
