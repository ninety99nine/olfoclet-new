<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class DeploymentResource extends BaseResource
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
            'id'                       => $this->id,
            'app_id'                   => $this->app_id,
            'country'                  => $this->country,
            'network'                  => $this->network,
            'active'                   => $this->active,
            'individual_replies'       => $this->individual_replies,
            'max_character_length'     => $this->max_character_length,

            'incoming_format'          => $this->incoming_format,
            'transform_request_language' => $this->transform_request_language,
            'transform_request_code'   => $this->transform_request_code,

            'outgoing_format'          => $this->outgoing_format,
            'transform_response_language' => $this->transform_response_language,
            'transform_response_code'  => $this->transform_response_code,

            'created_at'               => $this->created_at->toDateTimeString(),
            'updated_at'               => $this->updated_at->toDateTimeString(),
        ];
    }
}
