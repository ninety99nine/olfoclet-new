<?php

namespace App\Policies;

use App\Models\User;
use App\Services\AuthService;
use Illuminate\Support\Facades\DB;

class BasePolicy
{
    /**
     * @var AuthService
     */
    protected $authService;

    /**
     * Create a new policy instance.
     *
     * @param AuthService $authService
     */
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Check if the user has the specified permission within the app.
     *
     * @param User $user
     * @param string $ability
     * @return bool
     */
    protected function isAppUserWithPermission(User $user, string $ability, $appId = null): bool
    {
        $appId = $appId ?? getPermissionsTeamId();
        if (!$appId) return false;

        return DB::table('app_user')
            ->join('roles', 'app_user.role_id', '=', 'roles.id')
            ->join('role_has_permissions', 'roles.id', '=', 'role_has_permissions.role_id')
            ->join('permissions', 'role_has_permissions.permission_id', '=', 'permissions.id')
            ->where('app_user.user_id', $user->id)
            ->where('app_user.app_id', $appId)
            ->where('permissions.name', $ability)
            ->exists();
    }
}
