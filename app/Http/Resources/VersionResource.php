<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class VersionResource extends BaseResource
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
            'id'                => $this->id,
            'number'            => $this->number,
            'builder'           => $this->builder,
            'description'       => $this->description,

            'created_at'        => $this->created_at->toDateTimeString(),
            'updated_at'        => $this->updated_at->toDateTimeString(),

            'app'               => AppResource::make($this->whenLoaded('app')),
            'deployments'       => DeploymentResource::collection($this->whenLoaded('deployments')),
            'deployments_count' => $this->whenLoaded('deployments', fn() => $this->deployments->count()),

            'created_by' => $this->whenLoaded('createdBy', fn() => [
                'id' => $this->createdBy->id,
                'name' => $this->createdBy->name,
                'first_name' => $this->createdBy->first_name
            ])
        ];
    }
}
