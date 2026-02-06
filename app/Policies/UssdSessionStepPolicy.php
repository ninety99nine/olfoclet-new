<?php

namespace App\Policies;

use App\Models\App;
use App\Models\User;
use App\Models\UssdSession;
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
     *
     * @param User $user
     * @param App $app
     * @param UssdSession $ussdSession
     * @return bool
     */
    public function viewAny(User $user, App $app, UssdSession $ussdSession): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the session step.
     *
     * @param User $user
     * @param App $app
     * @param UssdSession $ussdSession
     * @param UssdSessionStep $ussdSessionStep
     * @return bool
     */
    public function view(User $user, App $app, UssdSession $ussdSession, UssdSessionStep $ussdSessionStep): bool
    {
        return true;
    }
}
