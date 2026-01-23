<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UssdSessionFlag;

class UssdSessionFlagPolicy extends BasePolicy
{
    /**
     * Grant all permissions to super admins.
     */
    public function before(User $user, string $ability): bool|null
    {
        return $this->authService->isSuperAdmin($user) ?: null;
    }

    /**
     * Determine whether the user can view any session flags.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the session flag.
     */
    public function view(User $user, UssdSessionFlag $ussdSessionFlag): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create session flags.
     *
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update a session flag.
     */
    public function update(User $user, UssdSessionFlag $ussdSessionFlag): bool
    {
        return true;
    }

    /**
     * Determine whether the user can delete a session flag.
     */
    public function delete(User $user, UssdSessionFlag $ussdSessionFlag): bool
    {
        return true;
    }
}
