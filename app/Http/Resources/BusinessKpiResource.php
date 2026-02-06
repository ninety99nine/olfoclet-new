<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class BusinessKpiResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'app_id' => $this->app_id,
            'name' => $this->name,
            'description' => $this->description,
            'metricType' => $this->metric_type,
            'chartType' => $this->chart_type,
            'breakDownByNetwork' => (bool) $this->break_down_by_network,
            'breakDownByCountry' => (bool) $this->break_down_by_country,
            'breakdownDisplay' => $this->breakdown_display,
            'currency' => $this->currency,
            'distributionOptions' => $this->distribution_options ?? [],
            'xAxisName' => $this->x_axis_name,
            'yAxisName' => $this->y_axis_name,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
