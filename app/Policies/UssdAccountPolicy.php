<?php

namespace App\Policies;

use App\Models\App;
use App\Models\User;
use App\Models\UssdAccount;

class UssdAccountPolicy extends BasePolicy
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
     * Determine whether the user can view any accounts.
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
     * Determine whether the user can view the account.
     *
     * @param User $user
     * @param App $app
     * @param UssdAccount $ussdAccount
     * @return bool
     */
    public function view(User $user, App $app, UssdAccount $ussdAccount): bool
    {
        return true;
    }

    /**
     * Determine whether the user can block any accounts.
     *
     * @param User $user
     * @param App $app
     * @return bool
     */
    public function blockAny(User $user, App $app): bool
    {
        return true;
    }

    /**
     * Determine whether the user can block the account.
     *
     * @param User $user
     * @param App $app
     * @param UssdAccount $ussdAccount
     * @return bool
     */
    public function block(User $user, App $app, UssdAccount $ussdAccount): bool
    {
        return true;
    }

    /**
     * Determine whether the user can unblock the account.
     *
     * @param User $user
     * @param App $app
     * @param UssdAccount $ussdAccount
     * @return bool
     */
    public function unblock(User $user, App $app, UssdAccount $ussdAccount): bool
    {
        return true;
    }

    /**
     * Determine whether the user can delete any accounts.
     *
     * @param User $user
     * @param App $app
     * @return bool
     */
    public function deleteAny(User $user, App $app): bool
    {
        return true;
    }

    /**
     * Determine whether the user can delete the account.
     *
     * @param User $user
     * @param App $app
     * @param UssdAccount $ussdAccount
     * @return bool
     */
    public function delete(User $user, App $app, UssdAccount $ussdAccount): bool
    {
        return true;
    }
}
