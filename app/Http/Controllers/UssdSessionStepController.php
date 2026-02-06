<?php

namespace App\Http\Controllers;

use App\Models\UssdSessionStep;
use App\Services\UssdSessionStepService;
use App\Http\Resources\UssdSessionStepResource;
use App\Http\Resources\UssdSessionStepResources;
use App\Http\Requests\UssdSessionStep\ShowUssdSessionStepsRequest;
use App\Http\Requests\UssdSessionStep\ShowUssdSessionStepRequest;
use App\Models\App;
use App\Models\UssdSession;

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
     * @param ShowUssdSessionStepsRequest $request
     * @param App $app
     * @param UssdSession $ussd_session
     * @return UssdSessionStepResources|array
     */
    public function showUssdSessionSteps(ShowUssdSessionStepsRequest $request, App $app, UssdSession $ussd_session): UssdSessionStepResources|array
    {
        return $this->service->showUssdSessionSteps($app, $ussd_session, $request->validated());
    }

    /**
     * Show ussd session step.
     *
     * @param ShowUssdSessionStepRequest $request
     * @param App $app
     * @param UssdSession $ussd_session
     * @param UssdSessionStep $ussd_session_step
     * @return UssdSessionStepResource
     */
    public function showUssdSessionStep(ShowUssdSessionStepRequest $request, App $app, UssdSession $ussd_session, UssdSessionStep $ussd_session_step): UssdSessionStepResource
    {
        return $this->service->showUssdSessionStep($app, $ussd_session, $ussd_session_step);
    }
}
