<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class UssdSessionResource extends BaseResource
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
            'id'                        => $this->id,
            'msisdn'                    => $this->msisdn,
            'shortcode'                 => $this->shortcode,
            'country'                   => $this->country,
            'network'                   => $this->network,
            'session_id'                => $this->session_id,
            'ussd_account_id'           => $this->ussd_account_id,

            'avg_wait_time_ms'          => $this->avg_wait_time_ms,
            'avg_wait_time_status'      => $this->getDurationStatus($this->avg_wait_time_ms),

            'successful'                => $this->successful,
            'error_message'             => $this->error_message,
            'total_steps'               => $this->total_steps,
            'open_flags_count'          => $this->open_flags_count,
            'simulated'                 => $this->simulated,
            'last_step_content'         => $this->last_step_content,
            'total_duration_ms'         => $this->total_duration_ms,

            'created_at'                => $this->created_at->toDateTimeString(),
            'updated_at'                => $this->updated_at->toDateTimeString(),

            'steps' => UssdSessionStepResource::collection($this->whenLoaded('steps')),
        ];
    }
}
