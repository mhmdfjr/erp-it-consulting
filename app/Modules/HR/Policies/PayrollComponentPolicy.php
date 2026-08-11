<?php

namespace App\Modules\HR\Policies;

use App\Models\User;
use App\Modules\HR\Models\PayrollComponent;

class PayrollComponentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('hr.payrollcomponent.view');
    }

    public function create(User $user): bool
    {
        return $user->can('hr.payrollcomponent.manage');
    }

    public function update(User $user, PayrollComponent $payrollComponent): bool
    {
        return $user->can('hr.payrollcomponent.manage');
    }
}
