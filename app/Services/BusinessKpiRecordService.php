<?php

namespace App\Services;

use App\Models\App;
use App\Models\BusinessKpi;
use App\Models\BusinessKpiRecord;
use App\Http\Resources\BusinessKpiRecordResource;
use App\Http\Resources\BusinessKpiRecordResources;

class BusinessKpiRecordService extends BaseService
{
    /**
     * Show business KPI records.
     *
     * @param App $app
     * @param BusinessKpi $businessKpi
     * @param array $data
     * @return BusinessKpiRecordResources|array
     */
    public function showBusinessKpiRecords(App $app, BusinessKpi $businessKpi, array $data): BusinessKpiRecordResources|array
    {
        $query = BusinessKpiRecord::where('business_kpi_id', $businessKpi->id);

        if (!request()->has('_sort')) {
            $query = $query->latest();
        }

        return $this->setQuery($query)->getOutput();
    }

    /**
     * Show business KPI record.
     *
     * @param App $app
     * @param BusinessKpi $businessKpi
     * @param BusinessKpiRecord $businessKpiRecord
     * @return BusinessKpiRecordResource
     */
    public function showBusinessKpiRecord(App $app, BusinessKpi $businessKpi, BusinessKpiRecord $businessKpiRecord): BusinessKpiRecordResource
    {
        return $this->showResource($businessKpiRecord);
    }
}
