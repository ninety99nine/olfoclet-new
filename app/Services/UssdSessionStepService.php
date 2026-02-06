<?php

namespace App\Services;

use App\Models\App;
use App\Models\UssdSession;
use App\Models\UssdSessionStep;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\UssdSessionStepResource;
use App\Http\Resources\UssdSessionStepResources;

class UssdSessionStepService extends BaseService
{
    /**
     * Show ussd session steps.
     *
     * @param App $app
     * @param UssdSession $ussd_session
     * @param array $data
     * @return UssdSessionStepResources|array
     */
    public function showUssdSessionSteps(App $app, UssdSession $ussd_session, array $data): UssdSessionStepResources|array
    {
        /** @var User $user */
        $user = Auth::user();

        $paginated = isset($data['paginated']) ?? null;
        $successful = isset($data['successful']) ?? null;
        $terminatedBySystem = isset($data['terminated_by_system']) ?? null;

        $query = UssdSessionStep::where('ussd_session_id', $ussd_session->id);

        if ($paginated !== null)            $query->where('paginated', $paginated);
        if ($successful !== null)           $query->where('successful', $successful);
        if ($terminatedBySystem !== null)   $query->where('terminated_by_system', $terminatedBySystem);

        if (!request()->has('_sort')) $query = $query->latest();

        return $this->setQuery($query)->getOutput();
    }

    /**
     * Show ussd session step.
     *
     * @param App $app
     * @param UssdSession $ussd_session
     * @param UssdSessionStep $ussd_session_step
     * @return UssdSessionStepResource
     */
    public function showUssdSessionStep(App $app, UssdSession $ussd_session, UssdSessionStep $ussd_session_step): UssdSessionStepResource
    {
        return $this->showResource($ussd_session_step);
    }
}
