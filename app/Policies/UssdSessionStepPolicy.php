<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UssdSessionStep;

class UssdSessionStepPolicy extends BasePolicy
{
    /**
     * Grant all permissions to super admins.
     *
     * @param User $user
     * @param string $ability
     * @return bool|null
     */
    public function before(User $user, string $ability): bool|null
    {
        return $this->authService->isSuperAdmin($user) ?: null;
    }

    /**
     * Determine whether the user can view any session steps.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the session step.
     */
    public function view(User $user, UssdSessionStep $ussdSessionStep): bool
    {
        return true;
    }
}
