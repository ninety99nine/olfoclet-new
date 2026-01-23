<?php

namespace App\Services;

use App\Enums\Association;
use App\Models\UssdSessionStep;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\UssdSessionStepResource;
use App\Http\Resources\UssdSessionStepResources;

class UssdSessionStepService extends BaseService
{
    /**
     * Show USSD session steps.
     *
     * @param array $data
     * @return UssdSessionStepResources|array
     */
    public function showUssdSessionSteps(array $data): UssdSessionStepResources|array
    {
        /** @var User $user */
        $user = Auth::user();

        $paginated = isset($data['paginated']) ?? null;
        $successful = isset($data['successful']) ?? null;
        $ussdSessionId = $data['ussd_session_id'] ?? null;
        $terminatedBySystem = isset($data['terminated_by_system']) ?? null;
        $association = isset($data['association']) ? Association::tryFrom($data['association']) : null;

        if ($association === Association::SUPER_ADMIN) {
            $query = UssdSessionStep::query()->latest();
        } else if (!empty($ussdSessionId)) {
            $query = UssdSessionStep::where('ussd_session_id', $ussdSessionId);
        } else {
            $appIds = $user->apps()->pluck('apps.id');
            $query = UssdSessionStep::whereIn('ussd_session_id', function ($q) use ($appIds) {
                $q->select('id')
                  ->from('ussd_sessions')
                  ->whereIn('app_id', $appIds);
            });
        }

        if ($paginated !== null)            $query->where('paginated', $paginated);
        if ($successful !== null)           $query->where('successful', $successful);
        if ($terminatedBySystem !== null)   $query->where('terminated_by_system', $terminatedBySystem);

        if (!request()->has('_sort')) $query = $query->latest();

        return $this->setQuery($query)->getOutput();
    }

    /**
     * Show a single USSD session step.
     *
     * @param UssdSessionStep $ussdSessionStep
     * @return UssdSessionStepResource
     */
    public function showUssdSessionStep(UssdSessionStep $ussdSessionStep): UssdSessionStepResource
    {
        return $this->showResource($ussdSessionStep);
    }
}
