<?php

namespace App\Policies;

use App\Models\App;
use App\Models\User;
use App\Models\UssdSession;

class UssdSessionPolicy extends BasePolicy
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
     * Determine whether the user can view any sessions.
     *
     * @param User $user
     * @param App $app
     * @return bool
     */
    public function viewAny(User $user, App $app): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the session.
     *
     * @param User $user
     * @param App $app
     * @param UssdSession $ussdSession
     * @return bool
     */
    public function view(User $user, App $app, UssdSession $ussdSession): bool
    {
        return true;
    }
}
