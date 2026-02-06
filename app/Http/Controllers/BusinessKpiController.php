<?php

namespace App\Http\Controllers;

use App\Models\App;
use App\Models\BusinessKpi;
use App\Services\BusinessKpiService;
use App\Http\Resources\BusinessKpiResource;
use App\Http\Resources\BusinessKpiResources;
use App\Http\Requests\BusinessKpi\ShowBusinessKpiRequest;
use App\Http\Requests\BusinessKpi\ShowBusinessKpisRequest;
use App\Http\Requests\BusinessKpi\CreateBusinessKpiRequest;
use App\Http\Requests\BusinessKpi\UpdateBusinessKpiRequest;
use App\Http\Requests\BusinessKpi\DeleteBusinessKpiRequest;

class BusinessKpiController extends Controller
{
    /**
     * @var BusinessKpiService
     */
    protected BusinessKpiService $service;

    /**
     * BusinessKpiController constructor.
     *
     * @param BusinessKpiService $service
     */
    public function __construct(BusinessKpiService $service)
    {
        $this->service = $service;
    }

    /**
     * Show business KPIs.
     *
     * @param ShowBusinessKpisRequest $request
     * @param App $app
     * @return BusinessKpiResources|array
     */
    public function showBusinessKpis(ShowBusinessKpisRequest $request, App $app): BusinessKpiResources|array
    {
        return $this->service->showBusinessKpis($app, $request->validated());
    }

    /**
     * Create business KPI.
     *
     * @param CreateBusinessKpiRequest $request
     * @param App $app
     * @return array
     */
    public function createBusinessKpi(CreateBusinessKpiRequest $request, App $app): array
    {
        return $this->service->createBusinessKpi($app, $request->validated());
    }

    /**
     * Show business KPI.
     *
     * @param ShowBusinessKpiRequest $request
     * @param App $app
     * @param BusinessKpi $businessKpi
     * @return BusinessKpiResource
     */
    public function showBusinessKpi(ShowBusinessKpiRequest $request, App $app, BusinessKpi $businessKpi): BusinessKpiResource
    {
        return $this->service->showBusinessKpi($app, $businessKpi);
    }

    /**
     * Update business KPI.
     *
     * @param UpdateBusinessKpiRequest $request
     * @param App $app
     * @param BusinessKpi $businessKpi
     * @return array
     */
    public function updateBusinessKpi(UpdateBusinessKpiRequest $request, App $app, BusinessKpi $businessKpi): array
    {
        return $this->service->updateBusinessKpi($app, $businessKpi, $request->validated());
    }

    /**
     * Delete business KPI.
     *
     * @param DeleteBusinessKpiRequest $request
     * @param App $app
     * @param BusinessKpi $businessKpi
     * @return array
     */
    public function deleteBusinessKpi(DeleteBusinessKpiRequest $request, App $app, BusinessKpi $businessKpi): array
    {
        return $this->service->deleteBusinessKpi($app, $businessKpi);
    }
}
