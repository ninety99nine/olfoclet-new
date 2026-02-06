<?php

namespace App\Policies;

use App\Models\App;
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
     * Determine whether the user can view the session flag.
     *
     * @param User $user
     * @param App $app
     * @param UssdSessionFlag $ussdSessionFlag
     * @return bool
     */
    public function view(User $user, App $app, UssdSessionFlag $ussdSessionFlag): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create session flags.
     *
     * @param User $user
     * @param App $app
     * @return bool
     */
    public function create(User $user, App $app): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update a session flag.
     *
     * @param User $user
     * @param App $app
     * @param UssdSessionFlag $ussdSessionFlag
     * @return bool
     */
    public function update(User $user, App $app, UssdSessionFlag $ussdSessionFlag): bool
    {
        return true;
    }

    /**
     * Determine whether the user can delete a session flag.
     *
     * @param User $user
     * @param App $app
     * @param UssdSessionFlag $ussdSessionFlag
     * @return bool
     */
    public function delete(User $user, App $app, UssdSessionFlag $ussdSessionFlag): bool
    {
        return true;
    }
}
