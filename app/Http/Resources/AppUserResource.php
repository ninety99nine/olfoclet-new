<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class AppUserResource extends BaseResource
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
            'email' => $this->email,
            'creator' => $this->creator,
            'user_id' => $this->user_id,
            'role_id' => $this->role_id,
            'app_id' => $this->app_id,
            'first_name' => $this->first_name,
            'joined_at' => $this->joined_at?->toDateTimeString(),
            'invited_at' => $this->invited_at?->toDateTimeString(),

            'user' => UserResource::make($this->whenLoaded('user')),
            'role' => RoleResource::make($this->whenLoaded('role')),
            'App' => AppResource::make($this->whenLoaded('App')),
        ];
    }
}
