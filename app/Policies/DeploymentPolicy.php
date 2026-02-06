<?php

namespace App\Policies;

use App\Models\App;
use App\Models\User;
use App\Models\Deployment;

class DeploymentPolicy extends BasePolicy
{
    /**
     * Grant all permissions to super admins.
     */
    public function before(User $user, string $ability): bool|null
    {
        return $this->authService->isSuperAdmin($user) ?: null;
    }

    /**
     * Determine whether the user can view any deployments for the app.
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
     * Determine whether the user can view a specific deployment.
     *
     * @param User $user
     * @param App $app
     * @param Deployment $deployment
     * @return bool
     */
    public function view(User $user, App $app, Deployment $deployment): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create deployments.
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
     * Determine whether the user can update a deployment.
     *
     * @param User $user
     * @param App $app
     * @param Deployment $deployment
     * @return bool
     */
    public function update(User $user, App $app, Deployment $deployment): bool
    {
        return true;
    }

    /**
     * Determine whether the user can delete any deployments (bulk).
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
     * Determine whether the user can delete a deployment.
     *
     * @param User $user
     * @param App $app
     * @param Deployment $deployment
     * @return bool
     */
    public function delete(User $user, App $app, Deployment $deployment): bool
    {
        return true;
    }
}
