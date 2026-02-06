<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BusinessKpiRecord extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'metric_value' => 'decimal:4'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'business_kpi_id','ussd_session_id','metric_value',
    ];

    /**
     * Get the business KPI this record belongs to.
     *
     * @return BelongsTo
     */
    public function businessKpi(): BelongsTo
    {
        return $this->belongsTo(BusinessKpi::class);
    }

    /**
     * Get the USSD session this record is linked to (if any).
     *
     * @return BelongsTo
     */
    public function ussdSession(): BelongsTo
    {
        return $this->belongsTo(UssdSession::class);
    }
}
