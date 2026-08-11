<?php

namespace App\Modules\HR\Policies;

use App\Models\User;
use App\Modules\HR\Models\Department;

class DepartmentPolicy
{
    public function create(User $user): bool
    {
        return $user->can('hr.department.manage');
    }

    public function update(User $user, Department $department): bool
    {
        return $user->can('hr.department.manage');
    }
}
