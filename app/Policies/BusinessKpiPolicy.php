<?php

namespace App\Policies;

use App\Models\App;
use App\Models\BusinessKpi;
use App\Models\User;

class BusinessKpiPolicy extends BasePolicy
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
        return $this->authService->isSuperAdmin($user) ? true : null;
    }

    /**
     * Determine whether the user can view any business KPIs for the app.
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
     * Determine whether the user can view the business KPI.
     *
     * @param User $user
     * @param App $app
     * @param BusinessKpi $businessKpi
     * @return bool
     */
    public function view(User $user, App $app, BusinessKpi $businessKpi): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create business KPIs for the app.
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
     * Determine whether the user can update the business KPI.
     *
     * @param User $user
     * @param App $app
     * @param BusinessKpi $businessKpi
     * @return bool
     */
    public function update(User $user, App $app, BusinessKpi $businessKpi): bool
    {
        return true;
    }

    /**
     * Determine whether the user can delete the business KPI.
     *
     * @param User $user
     * @param App $app
     * @param BusinessKpi $businessKpi
     * @return bool
     */
    public function delete(User $user, App $app, BusinessKpi $businessKpi): bool
    {
        return true;
    }
}
