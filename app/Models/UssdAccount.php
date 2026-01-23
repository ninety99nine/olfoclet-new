<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UssdAccount extends Model
{
    use HasFactory, HasUuids;

    protected $daysUntilInactive = 30;

    protected $table = 'ussd_accounts';

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'simulated' => 'boolean',
        'blocked_at' => 'datetime',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'msisdn','country','network','simulated','total_sessions','total_failed_sessions',
        'total_successful_sessions','total_mobile_sessions','total_simulator_sessions',
        'total_steps','total_duration_ms','blocked_at','open_flags_count',
        'app_id'
    ];

    /**
     * Scope a query by search term (msisdn).
     *
     * @param Builder $query
     * @param string $searchTerm
     * @return void
     */
    #[Scope]
    protected function search(Builder $query, string $searchTerm): void
    {
        $query->where('msisdn', 'like', '%' . $searchTerm . '%');
    }

    /**
     * Get all sessions for this account.
     *
     * @return HasMany
     */
    public function sessions(): HasMany
    {
        return $this->hasMany(UssdSession::class);
    }

    /**
     * Get the last few sessions.
     *
     * @return HasMany
     */
    public function lastFewSessions(): HasMany
    {
        return $this->sessions()->latest('created_at')->limit(3);
    }

    /**
     * Get the user's active status.
     */
    protected function isActive(): Attribute
    {
        return Attribute::make(
            get: function (): string {

                if ($this->blocked_at !== null) {
                    return false;
                }

                $lastActivity = $this->updated_at ?? $this->created_at;

                if (!$lastActivity) {
                    return false;
                }

                $threshold = now()->subDays($this->daysUntilInactive);

                return $lastActivity->gte($threshold);

            }

        );
    }

    /**
     * Get the user's status.
     */
    protected function status(): Attribute
    {
        return Attribute::make(
            get: function (): string {

                if ($this->blocked_at !== null) {
                    return 'Blocked';
                }

                $lastActivity = $this->updated_at ?? $this->created_at;

                if (!$lastActivity) {
                    return 'Inactive';
                }

                $threshold = now()->subDays($this->daysUntilInactive);

                return $lastActivity->gte($threshold) ? 'Active' : 'Inactive';

            }

        );
    }
}
