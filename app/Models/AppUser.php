<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\belongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Concerns\AsPivot;

class AppUser extends Model
{
    use HasFactory, HasUuids, AsPivot;

    protected $table = 'app_user';

    /**
     * Disable automatic timestamps since this table doesn't have created_at/updated_at columns
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected function casts(): array
    {
        return [
            'creator' => 'boolean',
            'joined_at' => 'datetime',
            'invited_at' => 'datetime',
            'last_seen_at' => 'datetime',
        ];
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'first_name', 'email', 'user_id', 'role_id', 'app_id', 'creator', 'invited_at', 'joined_at', 'last_seen_at'
    ];

    /**
     * Get role.
     *
     * @return belongsTo
     */
    public function role(): belongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get user.
     *
     * @return belongsTo
     */
    public function user(): belongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get app.
     *
     * @return belongsTo
     */
    public function app(): belongsTo
    {
        return $this->belongsTo(App::class);
    }
}
