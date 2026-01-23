<?php

namespace App\Http\Controllers;

use App\Models\UssdSession;
use App\Services\UssdSessionService;
use App\Http\Resources\UssdSessionResource;
use App\Http\Resources\UssdSessionResources;
use App\Http\Requests\UssdSession\ShowUssdSessionRequest;
use App\Http\Requests\UssdSession\ShowUssdSessionsRequest;
use App\Http\Requests\UssdSession\ShowUssdSessionsSummaryRequest;

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
     * @return UssdSessionResources|array
     */
    public function showUssdSessions(ShowUssdSessionsRequest $request): UssdSessionResources|array
    {
        return $this->service->showUssdSessions($request->validated());
    }

    /**
     * Show ussd sessions summary.
     *
     * @param ShowUssdSessionsSummaryRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function showUssdSessionsSummary(ShowUssdSessionsSummaryRequest $request)
    {
        return $this->service->showUssdSessionsSummary($request->validated());
    }

    /**
     * Show ussd session.
     *
     * @param ShowUssdSessionRequest $request
     * @param UssdSession $ussd_session
     * @return UssdSessionResource
     */
    public function showUssdSession(ShowUssdSessionRequest $request, UssdSession $ussd_session): UssdSessionResource
    {
        return $this->service->showUssdSession($ussd_session);
    }
}
