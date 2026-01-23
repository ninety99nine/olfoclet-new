<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UssdAccountResource extends JsonResource
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
            'status' => $this->status,
            'app_id' => $this->app_id,
            'msisdn' => $this->msisdn,
            'country' => $this->country,
            'network' => $this->network,
            'simulated' => $this->simulated,
            'is_active' => $this->is_active ? true : false,

            'total_sessions' => $this->total_sessions,
            'total_successful_sessions' => $this->total_successful_sessions,
            'total_failed_sessions' => $this->total_failed_sessions,
            'success_rate' => $this->total_sessions > 0
                ? round(($this->total_successful_sessions / $this->total_sessions) * 100, 1)
                : 0,
            'fail_rate' => $this->total_sessions > 0
                ? round(($this->total_failed_sessions / $this->total_sessions) * 100, 1)
                : 0,

            'total_mobile_sessions' => $this->total_mobile_sessions,
            'total_simulator_sessions' => $this->total_simulator_sessions,
            'mobile_sessions_percentage' => $this->total_sessions > 0
                ? round(($this->total_mobile_sessions / $this->total_sessions) * 100, 1)
                : 0,
            'simulator_sessions_percentage' => $this->total_sessions > 0
                ? round(($this->total_simulator_sessions / $this->total_sessions) * 100, 1)
                : 0,

            'total_steps' => $this->total_steps,
            'total_duration_ms' => $this->total_duration_ms,
            'total_avg_duration' => $this->total_sessions > 0 ? round($this->total_duration_ms / $this->total_sessions) : 0,

            'open_flags_count' => $this->open_flags_count,
            'blocked' => !is_null($this->blocked_at),
            'blocked_at' => $this->blocked_at?->toDateTimeString(),

            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),

            'last_few_sessions' => UssdSessionResource::collection($this->whenLoaded('lastFewSessions')),
        ];
    }
}
