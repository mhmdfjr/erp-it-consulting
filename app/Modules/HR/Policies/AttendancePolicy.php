<?php

namespace App\Modules\HR\Policies;

use App\Models\User;
use App\Modules\HR\Models\Attendance;

class AttendancePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('hr.attendance.view');
    }

    public function create(User $user): bool
    {
        return $user->can('hr.attendance.manage');
    }

    public function update(User $user, Attendance $attendance): bool
    {
        return $user->can('hr.attendance.manage');
    }
}
