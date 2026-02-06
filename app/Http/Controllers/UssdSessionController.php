<?php

namespace App\Http\Controllers;

use App\Models\UssdSession;
use App\Services\UssdSessionService;
use App\Http\Resources\UssdSessionResource;
use App\Http\Resources\UssdSessionResources;
use App\Http\Requests\UssdSession\ShowUssdSessionRequest;
use App\Http\Requests\UssdSession\ShowUssdSessionsRequest;
use App\Http\Requests\UssdSession\ShowUssdSessionsSummaryRequest;
use App\Models\App;

class UssdSessionController extends Controller
{
    /**
     * @var UssdSessionService
     */
    protected $service;

    public function __construct(UssdSessionService $service)
    {
        $this->service = $service;
    }

    /**
     * Show ussd sessions.
     *
     * @param ShowUssdSessionsRequest $request
     * @param App $app
     * @return UssdSessionResources|array
     */
    public function showUssdSessions(ShowUssdSessionsRequest $request, App $app): UssdSessionResources|array
    {
        return $this->service->showUssdSessions($app, $request->validated());
    }

    /**
     * Show ussd sessions summary.
     *
     * @param ShowUssdSessionsSummaryRequest $request
     * @param App $app
     * @return \Illuminate\Http\JsonResponse
     */
    public function showUssdSessionsSummary(ShowUssdSessionsSummaryRequest $request, App $app)
    {
        return $this->service->showUssdSessionsSummary($app, $request->validated());
    }

    /**
     * Show ussd session.
     *
     * @param ShowUssdSessionRequest $request
     * @param App $app
     * @param UssdSession $ussd_session
     * @return UssdSessionResource
     */
    public function showUssdSession(ShowUssdSessionRequest $request, App $app, UssdSession $ussd_session): UssdSessionResource
    {
        return $this->service->showUssdSession($app, $ussd_session);
    }
}
