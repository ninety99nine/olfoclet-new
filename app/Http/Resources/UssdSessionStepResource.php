<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UssdSessionStepResource extends BaseResource
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
            'id'                      => $this->id,
            'step_id'                 => $this->step_id,
            'step_title'              => $this->step_title,
            'step_content'            => $this->step_content,
            'reply'                   => $this->reply,

            'paginated'               => $this->paginated,
            'page_number'             => $this->page_number,
            'total_pages'             => $this->total_pages,
            'terminated_by_system'    => $this->terminated_by_system,
            'total_failed_validation' => $this->total_failed_validation,

            'total_duration_ms'          => $this->total_duration_ms,
            'total_duration_status'      => $this->getDurationStatus($this->total_duration_ms),

            'successful'              => $this->successful,
            'error_message'           => $this->error_message,

            'created_at'              => $this->created_at->toDateTimeString(),
            'updated_at'              => $this->updated_at->toDateTimeString(),
        ];
    }
}
