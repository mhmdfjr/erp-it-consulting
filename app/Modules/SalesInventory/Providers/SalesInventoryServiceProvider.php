<?php

namespace App\Modules\SalesInventory\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use App\Modules\SalesInventory\Livewire\CreateOrder;

class SalesInventoryServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'sales');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        Livewire::component('sales-order.create-order', CreateOrder::class);

        // Gate::policy() manual kalau ada Policy kritikal untuk
    }
}
