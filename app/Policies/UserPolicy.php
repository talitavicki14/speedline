<?php
namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can manage the target user.
     * 
     * @param User $actor The authenticated user performing the action.
     * @param User $target The user being managed.
     * @return bool
     */
    public function manage(User $actor, User $target): bool
    {
        if ($target->role === 'owner') {
            return $actor->role === 'owner';
        }

        return true;
    }
}
