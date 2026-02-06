<?php

namespace App\Http\Controllers;

use App\Http\Requests\Analytics\AnalyticsOverviewRequest;
use App\Services\AnalyticsService;
use Illuminate\Http\JsonResponse;

class AnalyticsController extends Controller
{
    protected AnalyticsService $service;

    public function __construct(AnalyticsService $service)
    {
        $this->service = $service;
    }

    public function overview(AnalyticsOverviewRequest $request): JsonResponse
    {
        return response()->json(
            $this->service->getOverview($request->validated())
        );
    }

    public function sessionsOverTime(AnalyticsOverviewRequest $request): JsonResponse
    {
        return response()->json(
            $this->service->getSessionsOverTime($request->validated())
        );
    }

    public function newUsersOverTime(AnalyticsOverviewRequest $request): JsonResponse
    {
        return response()->json(
            $this->service->getNewUsersOverTime($request->validated())
        );
    }

    public function returnUsersOverTime(AnalyticsOverviewRequest $request): JsonResponse
    {
        return response()->json(
            $this->service->getReturnUsersOverTime($request->validated())
        );
    }

    public function byNetwork(AnalyticsOverviewRequest $request): JsonResponse
    {
        return response()->json(
            $this->service->getByNetwork($request->validated())
        );
    }

    public function byCountry(AnalyticsOverviewRequest $request): JsonResponse
    {
        return response()->json(
            $this->service->getByCountry($request->validated())
        );
    }

    public function byNetworkAndCountry(AnalyticsOverviewRequest $request): JsonResponse
    {
        return response()->json(
            $this->service->getByNetworkAndCountry($request->validated())
        );
    }

    public function byDevice(AnalyticsOverviewRequest $request): JsonResponse
    {
        return response()->json(
            $this->service->getByDevice($request->validated())
        );
    }

    public function heatmap(AnalyticsOverviewRequest $request): JsonResponse
    {
        return response()->json(
            $this->service->getPeakHoursHeatmap($request->validated())
        );
    }

    public function flagsStatus(AnalyticsOverviewRequest $request): JsonResponse
    {
        return response()->json($this->service->getFlagsStatus($request->validated()));
    }

    public function flagsByPriority(AnalyticsOverviewRequest $request): JsonResponse
    {
        return response()->json($this->service->getFlagsByPriority($request->validated()));
    }

    public function flagsByCategory(AnalyticsOverviewRequest $request): JsonResponse
    {
        return response()->json($this->service->getFlagsByCategory($request->validated()));
    }

    public function failedSessionsOverTime(AnalyticsOverviewRequest $request): JsonResponse
    {
        return response()->json(
            $this->service->getFailedSessionsOverTime($request->validated())
        );
    }
}
