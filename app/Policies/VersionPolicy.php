<?php

namespace App\Policies;

use App\Models\App;
use App\Models\Version;
use App\Models\User;

class VersionPolicy extends BasePolicy
{
    /**
     * Grant all permissions to super admins who have roles not tied to any app.
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
     * Determine whether the user can view any apps.
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
     * Determine whether the user can view the app.
     *
     * @param User $user
     * @param App $app
     * @param Version $app
     * @return bool
     */
    public function view(User $user, App $app, Version $version): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create apps.
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
     * Determine whether the user can update the app.
     *
     * @param User $user
     * @param App $app
     * @param Version $app
     * @return bool
     */
    public function update(User $user, App $app, Version $version): bool
    {
        return true;
    }

    /**
     * Determine whether the user can delete any apps.
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
     * Determine whether the user can delete the app.
     *
     * @param User $user
     * @param App $app
     * @param Version $app
     * @return bool
     */
    public function delete(User $user, App $app, Version $version): bool
    {
        return true;
    }
}
