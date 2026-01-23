<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class UserResource extends BaseResource
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
            'email' => $this->email,
            'last_name' => $this->last_name,
            'first_name' => $this->first_name,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            'email_verified_at' => $this->email_verified_at ? $this->email_verified_at->toDateTimeString() : null,

            'has_password' => !is_null($this->password),
            'has_google'   => !is_null($this->google_id),
            'has_facebook' => !is_null($this->facebook_id),
            'has_linkedin' => !is_null($this->linkedin_id),

            // Optional: count of social logins
            'social_login_count' => ($this->google_id ? 1 : 0) +
                                    ($this->facebook_id ? 1 : 0) +
                                    ($this->linkedin_id ? 1 : 0),

            'roles' => RoleResource::collection($this->whenLoaded('roles')),

            //  Pivot
            'app_user' => $this->app_user ? AppUserResource::make($this->app_user) : null,
        ];
    }
}
