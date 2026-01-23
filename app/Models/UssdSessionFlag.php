<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UssdSessionFlag extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'ussd_session_flags';

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'status' => 'string',
        'resolved_at' => 'datetime',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'category','priority','comment','status','resolved_at','resolution_comment',
        'created_by','resolved_by','ussd_session_id','ussd_session_step_id'
    ];

    /**
     * Scope a query to only open flags.
     *
     * @param Builder $query
     * @return void
     */
    #[Scope]
    protected function open(Builder $query): void
    {
        $query->where('status', 'open');
    }

    /**
     * Get the session this flag belongs to.
     *
     * @return BelongsTo
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(UssdSession::class, 'ussd_session_id');
    }

    /**
     * Get the user who created this flag.
     *
     * @return BelongsTo
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who resolved this flag.
     *
     * @return BelongsTo
     */
    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
