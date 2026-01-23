<?php

namespace App\Services;

use App\Enums\Association;
use App\Models\UssdSession;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\UssdSessionResource;
use App\Http\Resources\UssdSessionResources;
use Illuminate\Support\Facades\DB;

class UssdSessionService extends BaseService
{
    /**
     * Show USSD sessions.
     *
     * @param array $data
     * @return UssdSessionResources|array
     */
    public function showUssdSessions(array $data): UssdSessionResources|array
    {
        /** @var User $user */
        $user = Auth::user();

        $appId = $data['app_id'] ?? null;
        $msisdn = $data['msisdn'] ?? null;
        $country = $data['country'] ?? null;
        $network = $data['network'] ?? null;
        $hasFlags = $data['has_flags'] ?? null;
        $shortcode = $data['shortcode'] ?? null;
        $simulated = $data['simulated'] ?? null;
        $dateRange = $data['date_range'] ?? null;
        $successful = $data['successful'] ?? null;
        $dateRangeEnd = $data['date_range_end'] ?? null;
        $dateRangeStart = $data['date_range_start'] ?? null;
        $association = isset($data['association']) ? Association::tryFrom($data['association']) : null;

        if ($association === Association::SUPER_ADMIN) {
            $query = UssdSession::query()->latest();
        } else if (!empty($appId)) {
            $query = UssdSession::where('app_id', $appId);
        } else {
            $appIds = $user->apps()->pluck('apps.id');
            $query = UssdSession::whereIn('app_id', $appIds);
        }

        if (!empty($msisdn))        $query->where('msisdn', $msisdn);
        if (!empty($country))       $query->where('country', $country);
        if (!empty($network))       $query->where('network', $network);
        if ($simulated !== null)    $query->where('simulated', $simulated);
        if (!empty($shortcode))     $query->where('shortcode', $shortcode);
        if ($successful !== null)   $query->where('successful', $successful);
        if ($hasFlags !== null)     $query->where('open_flags_count', $hasFlags ? '>' : '=', 0);

        if($dateRange) {

            // Apply Date Range
            $query = $this->applyDateRange($query, $dateRange, $dateRangeStart, $dateRangeEnd);

        }

        if(!request()->has('_sort')) $query = $query->latest();

        return $this->setQuery($query)->getOutput();
    }

    /**
     * Show USSD sessions summary.
     *
     * @param array $data
     * @return array
     */
    public function showUssdSessionsSummary(array $data): array
    {
        /** @var User $user */
        $user = Auth::user();

        $appId = $data['app_id'] ?? null;
        $msisdn = $data['msisdn'] ?? null;
        $country = $data['country'] ?? null;
        $network = $data['network'] ?? null;
        $shortcode = $data['shortcode'] ?? null;
        $dateRange = $data['date_range'] ?? null;
        $successful = $data['successful'] ?? null;
        $hasFlags = isset($data['has_flags']) ?? false;
        $simulated = isset($data['simulated']) ?? false;
        $dateRangeEnd = $data['date_range_end'] ?? null;
        $dateRangeStart = $data['date_range_start'] ?? null;
        $association = isset($data['association']) ? Association::tryFrom($data['association']) : null;

        if ($association === Association::SUPER_ADMIN) {
            $query = UssdSession::query()->latest();
        } else if (!empty($appId)) {
            $query = UssdSession::where('app_id', $appId);
        } else {
            $appIds = $user->apps()->pluck('apps.id');
            $query = UssdSession::whereIn('app_id', $appIds);
        }

        if ($simulated)             $query->where('simulated', 1);
        if (!empty($msisdn))        $query->where('msisdn', $msisdn);
        if (!empty($country))       $query->where('country', $country);
        if (!empty($network))       $query->where('network', $network);
        if (!empty($shortcode))     $query->where('shortcode', $shortcode);
        if ($successful !== null)   $query->where('successful', $successful);
        if ($hasFlags)              $query->where('open_flags_count', '>', 0);

        // Apply Date Range
        $query = $this->applyDateRange($query, $dateRange, $dateRangeStart, $dateRangeEnd);

        // Apply Search
        $query = $this->setQuery($query)->applySearchOnQuery()->getQuery();

        // Calculate stats
        $total     = (clone $query)->count();
        $successfulCount = (clone $query)->where('successful', true)->count();
        $failedCount     = (clone $query)->where('successful', false)->count();

        $failRate    = $total > 0 ? round(($failedCount / $total) * 100, 1) : 0;
        $successRate = $total > 0 ? round(($successfulCount / $total) * 100, 1) : 0;

        // Avg duration in milliseconds (updated_at - created_at)
        $avgDurationMs = (clone $query)->avg(DB::raw('TIMESTAMPDIFF(MICROSECOND, created_at, updated_at) / 1000')) ?? 0;

        // Avg steps
        $avgSteps = (clone $query)->avg('total_steps') ?? 0;

        // Device breakdown
        $simulatorCount = (clone $query)->where('simulated', true)->count();
        $mobileCount    = (clone $query)->where('simulated', false)->count();

        $mobilePct    = $total > 0 ? round(($mobileCount / $total) * 100, 1) : 0;
        $simulatorPct = $total > 0 ? round(($simulatorCount / $total) * 100, 1) : 0;

        return [
            'total_sessions'          => $total,
            'successful_sessions'     => $successfulCount,
            'failed_sessions'         => $failedCount,
            'success_rate'            => $successRate,
            'fail_rate'               => $failRate,
            'avg_duration_ms'         => round($avgDurationMs),
            'avg_steps'               => round($avgSteps, 1),
            'mobile_sessions'         => $mobileCount,
            'simulator_sessions'      => $simulatorCount,
            'mobile_percentage'       => $mobilePct,
            'simulator_percentage'    => $simulatorPct,
        ];
    }

    /**
     * Show USSD session.
     *
     * @param UssdSession $ussdSession
     * @return UssdSessionResource
     */
    public function showUssdSession(UssdSession $ussdSession): UssdSessionResource
    {
        return $this->showResource($ussdSession);
    }
}
