<?php

namespace App\Modules\HR\Policies;

use App\Models\User;
use App\Modules\HR\Models\Employee;

class EmployeePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('hr.employee.view');
    }

    public function create(User $user): bool
    {
        return $user->can('hr.employee.create');
    }

    public function update(User $user, Employee $employee): bool
    {
        return $user->can('hr.employee.update');
    }
}
