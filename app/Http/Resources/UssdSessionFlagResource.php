<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class UssdSessionFlagResource extends BaseResource
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
            'id'                  => $this->id,
            'ussd_session_id'     => $this->ussd_session_id,
            'ussd_session_step_id'=> $this->ussd_session_step_id,
            'category'            => $this->category,
            'priority'            => $this->priority,
            'comment'             => $this->comment,
            'status'              => $this->status,
            'resolved'            => !is_null($this->resolved_at),
            'resolved_at'         => $this->resolved_at?->toDateTimeString(),
            'resolution_comment'  => $this->resolution_comment,
            'created_by'          => $this->created_by,
            'resolved_by'         => $this->resolved_by,

            'created_at'          => $this->created_at->toDateTimeString(),
            'updated_at'          => $this->updated_at->toDateTimeString(),

            'created_by' => UserResource::make($this->whenLoaded('createdBy')),
            'resolved_by' => UserResource::make($this->whenLoaded('resolvedBy')),
        ];
    }
}
