<?php

namespace App\Http\Controllers;

use App\Models\App;
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
     * @param App $app
     * @return UssdSessionFlagResources|array
     */
    public function showUssdSessionFlags(ShowUssdSessionFlagsRequest $request, App $app): UssdSessionFlagResources|array
    {
        return $this->service->showUssdSessionFlags($app, $request->validated());
    }

    /**
     * Create ussd session flag.
     *
     * @param CreateUssdSessionFlagRequest $request
     * @param App $app
     * @return array
     */
    public function createUssdSessionFlag(CreateUssdSessionFlagRequest $request, App $app): array
    {
        return $this->service->createUssdSessionFlag($app, $request->validated());
    }

    /**
     * Show USSD session flags summary.
     *
     * @param Request $request
     * @param App $app
     * @return \Illuminate\Http\JsonResponse
     */
    public function showUssdSessionFlagsSummary(ShowUssdSessionFlagsSummaryRequest $request, App $app)
    {
        return $this->service->showUssdSessionFlagsSummary($app, $request->validated());
    }

    /**
     * Show ussd session flag.
     *
     * @param ShowUssdSessionFlagRequest $request
     * @param App $app
     * @param UssdSessionFlag $ussd_session_flag
     * @return UssdSessionFlagResource
     */
    public function showUssdSessionFlag(ShowUssdSessionFlagRequest $request, App $app, UssdSessionFlag $ussd_session_flag): UssdSessionFlagResource
    {
        return $this->service->showUssdSessionFlag($app, $ussd_session_flag);
    }

    /**
     * Update ussd session flag.
     *
     * @param UpdateUssdSessionFlagRequest $request
     * @param App $app
     * @param UssdSessionFlag $ussd_session_flag
     * @return array
     */
    public function updateUssdSessionFlag(UpdateUssdSessionFlagRequest $request, App $app, UssdSessionFlag $ussd_session_flag): array
    {
        return $this->service->updateUssdSessionFlag($app, $ussd_session_flag, $request->validated());
    }

    /**
     * Delete ussd session flag.
     *
     * @param DeleteUssdSessionFlagRequest $request
     * @param App $app
     * @param UssdSessionFlag $ussd_session_flag
     * @return array
     */
    public function deleteUssdSessionFlag(DeleteUssdSessionFlagRequest $request, App $app, UssdSessionFlag $ussd_session_flag): array
    {
        return $this->service->deleteUssdSessionFlag($app, $ussd_session_flag);
    }

    /**
     * Resolve ussd session flag.
     *
     * @param ResolveUssdSessionFlagRequest $request
     * @param App $app
     * @param UssdSessionFlag $ussd_session_flag
     * @return array
     */
    public function resolveUssdSessionFlag(ResolveUssdSessionFlagRequest $request, App $app, UssdSessionFlag $ussd_session_flag): array
    {
        return $this->service->resolveUssdSessionFlag($app, $ussd_session_flag, $request->validated());
    }

    /**
     * Unresolve ussd session flag.
     *
     * @param UnresolveUssdSessionFlagRequest $request
     * @param App $app
     * @param UssdSessionFlag $ussd_session_flag
     * @return array
     */
    public function unresolveUssdSessionFlag(UnresolveUssdSessionFlagRequest $request, App $app, UssdSessionFlag $ussd_session_flag): array
    {
        return $this->service->unresolveUssdSessionFlag($app, $ussd_session_flag);
    }
}
