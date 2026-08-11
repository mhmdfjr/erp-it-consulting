<?php

namespace App\Modules\HR\Providers;

use App\Modules\HR\Models\Attendance;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use App\Modules\HR\Models\Department;
use App\Modules\HR\Models\Employee;
use App\Modules\HR\Models\PayrollPeriod;
use App\Modules\HR\Policies\DepartmentPolicy;
use App\Modules\HR\Models\Position;
use App\Modules\HR\Policies\AttendancePolicy;
use App\Modules\HR\Policies\EmployeePolicy;
use App\Modules\HR\Policies\PayrollPeriodPolicy;
use App\Modules\HR\Policies\PositionPolicy;
use App\Modules\HR\Models\PayrollComponent;
use App\Modules\HR\Policies\PayrollComponentPolicy;

class HRServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'hr');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        Gate::policy(Department::class, DepartmentPolicy::class);
        Gate::policy(Position::class, PositionPolicy::class);
        Gate::policy(Employee::class, EmployeePolicy::class);
        Gate::policy(PayrollPeriod::class, PayrollPeriodPolicy::class);
        Gate::policy(Attendance::class, AttendancePolicy::class);
        Gate::policy(PayrollComponent::class, PayrollComponentPolicy::class);
    }
}
