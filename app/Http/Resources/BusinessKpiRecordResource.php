<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class BusinessKpiRecordResource extends BaseResource
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
            'business_kpi_id' => $this->business_kpi_id,
            'session_id' => $this->ussd_session_id,
            'country' => $this->country,
            'network' => $this->network,
            'msisdn' => $this->msisdn,
            'total_duration_ms' => $this->total_duration_ms,
            'successful' => (bool) $this->successful,
            'total_steps' => $this->total_steps,
            'simulated' => (bool) $this->simulated,
            'metric_value' => $this->metric_value !== null ? (string) $this->metric_value : null,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
            'ussd_session' => UssdSessionResource::make($this->whenLoaded('ussdSession')),
        ];
    }
}
