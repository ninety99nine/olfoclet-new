<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BusinessKpi extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'distribution_options' => 'array',
        'break_down_by_network' => 'boolean',
        'break_down_by_country' => 'boolean',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'app_id','name','description','metric_type','chart_type','break_down_by_network','break_down_by_country',
        'breakdown_display','currency','distribution_options','x_axis_name','y_axis_name',
    ];

    /**
     * Get the app this business KPI belongs to.
     *
     * @return BelongsTo
     */
    public function app(): BelongsTo
    {
        return $this->belongsTo(App::class);
    }

    /**
     * Get the records for this business KPI.
     *
     * @return HasMany
     */
    public function businessKpiRecords(): HasMany
    {
        return $this->hasMany(BusinessKpiRecord::class);
    }
}
