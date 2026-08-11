<?php

namespace App\Modules\HR\Policies;

use App\Models\User;
use App\Modules\HR\Models\Position;

class PositionPolicy
{
    public function create(User $user): bool
    {
        return $user->can('hr.position.manage');
    }

    public function update(User $user, Position $position): bool
    {
        return $user->can('hr.position.manage');
    }
}
