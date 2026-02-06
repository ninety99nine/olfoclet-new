<?php

namespace App\Http\Controllers;

use App\Models\App;
use App\Models\BusinessKpi;
use App\Models\BusinessKpiRecord;
use App\Services\BusinessKpiRecordService;
use App\Http\Resources\BusinessKpiRecordResource;
use App\Http\Resources\BusinessKpiRecordResources;
use App\Http\Requests\BusinessKpiRecord\ShowBusinessKpiRecordRequest;
use App\Http\Requests\BusinessKpiRecord\ShowBusinessKpiRecordsRequest;

class BusinessKpiRecordController extends Controller
{
    /**
     * @var BusinessKpiRecordService
     */
    protected BusinessKpiRecordService $service;

    /**
     * BusinessKpiRecordController constructor.
     *
     * @param BusinessKpiRecordService $service
     */
    public function __construct(BusinessKpiRecordService $service)
    {
        $this->service = $service;
    }

    /**
     * Show business KPI records.
     *
     * @param ShowBusinessKpiRecordsRequest $request
     * @param App $app
     * @param BusinessKpi $businessKpi
     * @return \Illuminate\Http\Resources\Json\ResourceCollection|array
     */
    public function showBusinessKpiRecords(ShowBusinessKpiRecordsRequest $request, App $app, BusinessKpi $businessKpi): BusinessKpiRecordResources|array
    {
        return $this->service->showBusinessKpiRecords($app, $businessKpi, $request->validated());
    }

    /**
     * Show business KPI record.
     *
     * @param ShowBusinessKpiRecordRequest $request
     * @param App $app
     * @param BusinessKpi $businessKpi
     * @param BusinessKpiRecord $businessKpiRecord
     * @return BusinessKpiRecordResource
     */
    public function showBusinessKpiRecord(ShowBusinessKpiRecordRequest $request, App $app, BusinessKpi $businessKpi, BusinessKpiRecord $businessKpiRecord): BusinessKpiRecordResource
    {
        return $this->service->showBusinessKpiRecord($app, $businessKpi, $businessKpiRecord);
    }
}
