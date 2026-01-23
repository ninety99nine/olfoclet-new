<?php

namespace App\Http\Controllers;

use App\Models\UssdSessionFlag;
use App\Services\UssdSessionFlagService;
use App\Http\Resources\UssdSessionFlagResource;
use App\Http\Resources\UssdSessionFlagResources;
use App\Http\Requests\UssdSessionFlag\ShowUssdSessionFlagRequest;
use App\Http\Requests\UssdSessionFlag\ShowUssdSessionFlagsRequest;
use App\Http\Requests\UssdSessionFlag\UpdateUssdSessionFlagRequest;
use App\Http\Requests\UssdSessionFlag\DeleteUssdSessionFlagRequest;
use App\Http\Requests\UssdSessionFlag\CreateUssdSessionFlagRequest;
use App\Http\Requests\UssdSessionFlag\ResolveUssdSessionFlagRequest;
use App\Http\Requests\UssdSessionFlag\UnresolveUssdSessionFlagRequest;
use App\Http\Requests\UssdSessionFlag\ShowUssdSessionFlagsSummaryRequest;

class UssdSessionFlagController extends Controller
{
    /**
     * @var UssdSessionFlagService
     */
    protected $service;

    /**
     * UssdSessionFlagController constructor.
     *
     * @param UssdSessionFlagService $service
     */
    public function __construct(UssdSessionFlagService $service)
    {
        $this->service = $service;
    }

    /**
     * Show ussd session flags.
     *
     * @param ShowUssdSessionFlagsRequest $request
     * @return UssdSessionFlagResources|array
     */
    public function showUssdSessionFlags(ShowUssdSessionFlagsRequest $request): UssdSessionFlagResources|array
    {
        return $this->service->showUssdSessionFlags($request->validated());
    }

    /**
     * Create a new USSD session flag.
     */
    public function createUssdSessionFlag(CreateUssdSessionFlagRequest $request): array
    {
        return $this->service->createUssdSessionFlag($request->validated());
    }

    /**
     * Show summary statistics for USSD session flags.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function showUssdSessionFlagsSummary(ShowUssdSessionFlagsSummaryRequest $request)
    {
        return $this->service->showUssdSessionFlagsSummary($request->validated());
    }

    /**
     * Show ussd session flag.
     *
     * @param ShowUssdSessionFlagRequest $request
     * @param UssdSessionFlag $ussd_session_flag
     * @return UssdSessionFlagResource
     */
    public function showUssdSessionFlag(ShowUssdSessionFlagRequest $request, UssdSessionFlag $ussd_session_flag): UssdSessionFlagResource
    {
        return $this->service->showUssdSessionFlag($ussd_session_flag);
    }

    /**
     * Update ussd session flag.
     *
     * @param UpdateUssdSessionFlagRequest $request
     * @param UssdSessionFlag $ussd_session_flag
     * @return array
     */
    public function updateUssdSessionFlag(UpdateUssdSessionFlagRequest $request, UssdSessionFlag $ussd_session_flag): array
    {
        return $this->service->updateUssdSessionFlag($ussd_session_flag, $request->validated());
    }

    /**
     * Delete ussd session flag.
     *
     * @param DeleteUssdSessionFlagRequest $request
     * @param UssdSessionFlag $ussd_session_flag
     * @return array
     */
    public function deleteUssdSessionFlag(DeleteUssdSessionFlagRequest $request, UssdSessionFlag $ussd_session_flag): array
    {
        return $this->service->deleteUssdSessionFlag($ussd_session_flag);
    }

    /**
     * Resolve ussd session flag.
     *
     * @param ResolveUssdSessionFlagRequest $request
     * @param UssdSessionFlag $ussd_session_flag
     * @return array
     */
    public function resolveUssdSessionFlag(ResolveUssdSessionFlagRequest $request, UssdSessionFlag $ussd_session_flag): array
    {
        return $this->service->resolveUssdSessionFlag($ussd_session_flag, $request->validated());
    }

    /**
     * Unresolve ussd session flag.
     *
     * @param UnresolveUssdSessionFlagRequest $request
     * @param UssdSessionFlag $ussd_session_flag
     * @return array
     */
    public function unresolveUssdSessionFlag(UnresolveUssdSessionFlagRequest $request, UssdSessionFlag $ussd_session_flag): array
    {
        return $this->service->unresolveUssdSessionFlag($ussd_session_flag);
    }
}
