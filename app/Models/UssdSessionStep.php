<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UssdSessionStep extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'ussd_session_steps';

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'paginated' => 'boolean',
        'successful' => 'boolean',
        'terminated_by_system' => 'boolean',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'step_id','step_title','step_content','reply','paginated','page_number','total_pages',
        'terminated_by_system','total_failed_validation','total_duration_ms',
        'successful','error_message','ussd_session_id'
    ];

    /**
     * Get the session this step belongs to.
     *
     * @return BelongsTo
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(UssdSession::class, 'ussd_session_id');
    }
}
