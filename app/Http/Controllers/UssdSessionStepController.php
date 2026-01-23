<?php

namespace App\Http\Controllers;

use App\Models\UssdSessionStep;
use App\Services\UssdSessionStepService;
use App\Http\Resources\UssdSessionStepResource;
use App\Http\Resources\UssdSessionStepResources;
use App\Http\Requests\UssdSessionStep\ShowUssdSessionStepsRequest;
use App\Http\Requests\UssdSessionStep\ShowUssdSessionStepRequest;

class UssdSessionStepController extends Controller
{
    /**
     * @var UssdSessionStepService
     */
    protected $service;

    /**
     * UssdSessionStepController constructor.
     *
     * @param UssdSessionStepService $service
     */
    public function __construct(UssdSessionStepService $service)
    {
        $this->service = $service;
    }

    /**
     * Show ussd session steps.
     *
     * @param ShowUssdSessionStepsRequest $request
     * @return UssdSessionStepResources|array
     */
    public function showUssdSessionSteps(ShowUssdSessionStepsRequest $request): UssdSessionStepResources|array
    {
        return $this->service->showUssdSessionSteps($request->validated());
    }

    /**
     * Show session step.
     *
     * @param ShowUssdSessionStepRequest $request
     * @param UssdSessionStep $ussd_session_step
     * @return UssdSessionStepResource
     */
    public function showUssdSessionStep(ShowUssdSessionStepRequest $request, UssdSessionStep $ussd_session_step): UssdSessionStepResource
    {
        return $this->service->showUssdSessionStep($ussd_session_step);
    }
}
