<?php

namespace App\Services;

use App\Models\App;
use App\Models\BusinessKpi;
use App\Http\Resources\BusinessKpiResource;
use App\Http\Resources\BusinessKpiResources;

class BusinessKpiService extends BaseService
{
    /**
     * Show business KPIs.
     *
     * @param App $app
     * @param array $data
     * @return BusinessKpiResources|array
     */
    public function showBusinessKpis(App $app, array $data): BusinessKpiResources|array
    {
        $query = BusinessKpi::where('app_id', $app->id);

        if (!request()->has('_sort')) {
            $query = $query->latest();
        }

        return $this->setQuery($query)->getOutput();
    }

    /**
     * Create business KPI.
     *
     * @param App $app
     * @param array $data
     * @return array
     */
    public function createBusinessKpi(App $app, array $data): array
    {
        $data['app_id'] = $app->id;
        $businessKpi = BusinessKpi::create($data);
        return $this->showCreatedResource($businessKpi);
    }

    /**
     * Show business KPI.
     *
     * @param App $app
     * @param BusinessKpi $businessKpi
     * @return BusinessKpiResource
     */
    public function showBusinessKpi(App $app, BusinessKpi $businessKpi): BusinessKpiResource
    {
        return $this->showResource($businessKpi);
    }

    /**
     * Update business KPI.
     *
     * @param App $app
     * @param BusinessKpi $businessKpi
     * @param array $data
     * @return array
     */
    public function updateBusinessKpi(App $app, BusinessKpi $businessKpi, array $data): array
    {
        $businessKpi->update($data);
        return $this->showUpdatedResource($businessKpi);
    }

    /**
     * Delete business KPI.
     *
     * @param App $app
     * @param BusinessKpi $businessKpi
     * @return array
     */
    public function deleteBusinessKpi(App $app, BusinessKpi $businessKpi): array
    {
        $businessKpi->delete();
        return [
            'deleted' => true,
            'message' => 'Business KPI deleted',
        ];
    }
}
