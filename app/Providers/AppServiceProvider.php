<?php

namespace App\Providers;

use App\Support\NotificationCenterService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use App\Modules\SalesInventory\Events\SalesOrderCompleted;
use App\Listeners\NotifyUsersOnSalesOrderCompleted;
use Illuminate\Support\Facades\Event;

class AppServiceProvider extends ServiceProvider
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
        // Event::listen(SalesOrderCompleted::class, NotifyUsersOnSalesOrderCompleted::class);

        View::composer('components.topbar', function ($view) {
            if (! Auth::check()) {
                return;
            }

            $user = Auth::user();
            $service = app(NotificationCenterService::class);

            $view->with([
                'persistedNotifications' => $user->unreadNotifications()->take(5)->get(), //Undefined method 'unreadNotifications'.intelephense(P1013)
                'unreadPersistedCount' => $user->unreadNotifications()->count(), //Undefined method 'unreadNotifications'.intelephense(P1013)
                'computedAlerts' => $service->computedAlertsFor($user),
            ]);
        });
    }
}
