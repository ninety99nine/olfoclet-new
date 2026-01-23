<?php

namespace App\Models;

use App\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Builder;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\belongsToMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, HasRoles, HasUuids;

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'email_verified_at' => 'datetime'
        ];
    }

    protected $fillable = [
        'first_name', 'last_name', 'email', 'google_id', 'facebook_id', 'linkedin_id', 'password', 'email_verified_at'
    ];

    protected $hidden = [
        'password', 'remember_token'
    ];

    /**
     * Scope a query by search term.
     *
     * @param Builder $query
     * @param string $searchTerm
     * @return void
     */
    #[Scope]
    protected function search(Builder $query, string $searchTerm): void
    {
        $query->where('name', 'like', '%' . $searchTerm . '%');
    }

    /**
     * Get apps.
     *
     * @return BelongsToMany
     */
    public function apps(): BelongsToMany
    {
        return $this->belongsToMany(App::class, 'app_user')
                    ->withPivot(['id', 'first_name', 'email', 'user_id', 'role_id', 'app_id', 'creator', 'invited_at', 'joined_at', 'last_seen_at'])
                    ->using(AppUser::class)
                    ->as('app_user');
    }

    /**
     * Get user's full name.
     *
     * @return Attribute
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn () => trim("{$this->first_name} {$this->last_name}")
        );
    }
}
