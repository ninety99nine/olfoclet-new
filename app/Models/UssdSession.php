<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class UssdSession extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'ussd_sessions';

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'successful' => 'boolean',
        'simulated' => 'boolean',
        'history' => 'array'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'msisdn','shortcode','country','network','session_id','successful','error_message',
        'total_steps','open_flags_count','simulated','last_step_content',
        'total_duration_ms','ussd_account_id','app_id',
        'history'
    ];

    /**
     * Scope a query by search term (msisdn or session_id).
     *
     * @param Builder $query
     * @param string $searchTerm
     * @return void
     */
    #[Scope]
    protected function search(Builder $query, string $searchTerm): void
    {
        $query->where(function ($q) use ($searchTerm) {
            $q->where('msisdn', 'like', '%' . $searchTerm . '%')
              ->orWhere('session_id', 'like', '%' . $searchTerm . '%');
        });
    }

    /**
     * Get the app this session belongs to.
     *
     * @return BelongsTo
     */
    public function app(): BelongsTo
    {
        return $this->belongsTo(App::class);
    }

    /**
     * Get the account this session belongs to.
     *
     * @return BelongsTo
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(UssdAccount::class, 'msisdn', 'msisdn');
    }

    /**
     * Get all steps in this session.
     *
     * @return HasMany
     */
    public function steps(): HasMany
    {
        return $this->hasMany(UssdSessionStep::class, 'ussd_session_id');
    }

    /**
     * Get all flags/reviews for this session.
     *
     * @return HasMany
     */
    public function flags(): HasMany
    {
        return $this->hasMany(UssdSessionFlag::class, 'ussd_session_id');
    }

    protected function avgWaitTimeMs(): Attribute
    {
        return Attribute::make(
            get: function (?int $value, array $attributes): ?float {

                $totalMs   = $attributes['total_duration_ms'] ?? 0;
                $steps     = $attributes['total_steps'] ?? 0;

                if ($steps <= 0 || $totalMs <= 0) {
                    return 0;
                }

                return round($totalMs / $steps);

            }
        );
    }
}
