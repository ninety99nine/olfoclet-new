<?php

namespace App\Policies;

use App\Models\App;
use App\Models\BusinessKpi;
use App\Models\BusinessKpiRecord;
use App\Models\User;

class BusinessKpiRecordPolicy extends BasePolicy
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
        return $this->authService->isSuperAdmin($user) ? true : null;
    }

    /**
     * Determine whether the user can view any records for the business KPI.
     *
     * @param User $user
     * @param App $app
     * @param BusinessKpi $businessKpi
     * @return bool
     */
    public function viewAny(User $user, App $app, BusinessKpi $businessKpi): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the record.
     *
     * @param User $user
     * @param App $app
     * @param BusinessKpi $businessKpi
     * @param BusinessKpiRecord $businessKpiRecord
     * @return bool
     */
    public function view(User $user, App $app, BusinessKpi $businessKpi, BusinessKpiRecord $businessKpiRecord): bool
    {
        return true;
    }
}
