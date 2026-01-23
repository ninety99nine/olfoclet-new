<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class AppResource extends BaseResource
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
            'name' => $this->name,
            'status' => $this->status,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),

            'logo' => MediaFileResource::make($this->whenLoaded('logo')),
            'deployments' => DeploymentResource::collection($this->whenLoaded('deployments')),

            //  Pivot
            'app_user' => $this->app_user ? AppUserResource::make($this->app_user) : null,
        ];
    }
}
