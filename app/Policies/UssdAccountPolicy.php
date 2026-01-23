<?php

namespace App\Policies;

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
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the account.
     */
    public function view(User $user, UssdAccount $ussdAccount): bool
    {
        return true;
    }

    /**
     * Determine whether the user can block any accounts.
     */
    public function blockAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can block the account.
     */
    public function block(User $user, UssdAccount $ussdAccount): bool
    {
        return true;
    }

    /**
     * Determine whether the user can unblock the account.
     */
    public function unblock(User $user, UssdAccount $ussdAccount): bool
    {
        return true;
    }

    /**
     * Determine whether the user can delete any accounts.
     */
    public function deleteAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can delete the account.
     */
    public function delete(User $user, UssdAccount $ussdAccount): bool
    {
        return true;
    }
}
