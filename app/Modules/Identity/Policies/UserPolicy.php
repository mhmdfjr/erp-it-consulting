<?php

namespace App\Modules\Identity\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('identity.user.view');
    }

    public function view(User $user, User $model): bool
    {
        return $user->can('identity.user.view');
    }

    public function create(User $user): bool
    {
        return $user->can('identity.user.create');
    }

    public function update(User $user, User $model): bool
    {
        return $user->can('identity.user.update');
    }
}
