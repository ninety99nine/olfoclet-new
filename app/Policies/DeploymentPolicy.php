<?php

namespace App\Policies;

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
     * Determine whether the user can view any deployments (list view).
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view a specific deployment.
     */
    public function view(User $user, Deployment $deployment): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create deployments.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update a deployment.
     */
    public function update(User $user, Deployment $deployment): bool
    {
        return true;
    }

    /**
     * Determine whether the user can delete any deployments (bulk).
     */
    public function deleteAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can delete a deployment.
     */
    public function delete(User $user, Deployment $deployment): bool
    {
        return true;
    }
}
