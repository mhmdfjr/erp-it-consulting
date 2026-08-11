<?php

namespace App\Modules\HR\Policies;

use App\Models\User;

class PayrollPeriodPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('hr.payroll.view');
    }

    public function create(User $user): bool
    {
        return $user->can('hr.payroll.process');
    }

    public function process(User $user): bool
    {
        return $user->can('hr.payroll.process');
    }

    public function markAsPaid(User $user): bool
    {
        return $user->can('hr.payroll.pay');
    }

    public function cancel(User $user): bool
    {
        return $user->can('hr.payroll.process');
    }
}
