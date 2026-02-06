<?php

namespace App\Services;

use App\Models\App;
use App\Models\UssdSessionFlag;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\UssdSessionFlagResource;
use App\Http\Resources\UssdSessionFlagResources;

class UssdSessionFlagService extends BaseService
{
    /**
     * Show ussd session flags.
     *
     * @param App $app
     * @param array $data
     * @return UssdSessionFlagResources|array
     */
    public function showUssdSessionFlags(App $app, array $data): UssdSessionFlagResources|array
    {
        $status         = $data['status'] ?? null;
        $priority       = $data['priority'] ?? null;
        $category       = $data['category'] ?? null;
        $dateRange      = $data['date_range'] ?? null;
        $dateRangeEnd   = $data['date_range_end'] ?? null;
        $ussdSessionId  = $data['ussd_session_id'] ?? null;
        $dateRangeStart = $data['date_range_start'] ?? null;

        if (!empty($ussdSessionId)) {
            $query = UssdSessionFlag::where('app_id', $app->id)->where('ussd_session_id', $ussdSessionId);
        } else {
            $query = UssdSessionFlag::where('app_id', $app->id);
        }

        if (!empty($status))    $query->where('status', $status);
        if (!empty($category))  $query->where('category', $category);
        if (!empty($priority))  $query->where('priority', $priority);

        if($dateRange) {

            // Apply Date Range
            $query = $this->applyDateRange($query, $dateRange, $dateRangeStart, $dateRangeEnd);

        }

        if (!request()->has('_sort')) $query = $query->latest();

        return $this->setQuery($query)->getOutput();
    }

    /**
     * Create ussd session flag.
     *
     * @param App $app
     * @param array $data
     * @return array
     */
    public function createUssdSessionFlag(App $app, array $data): array
    {
        $data = [
            ...$data,
            'app_id' => $app->id,
            'created_by' => Auth::user()->id
        ];

        $flag = UssdSessionFlag::create($data);

        return $this->showCreatedResource($flag);
    }

    /**
     * Show ussd session flags summary.
     *
     * @param App $app
     * @param array $data
     * @return array
     */
    public function showUssdSessionFlagsSummary(App $app, array $data): array
    {
        $status         = $data['status'] ?? null;
        $priority       = $data['priority'] ?? null;
        $category       = $data['category'] ?? null;
        $dateRange      = $data['date_range'] ?? null;
        $dateRangeEnd   = $data['date_range_end'] ?? null;
        $ussdSessionId  = $data['ussd_session_id'] ?? null;
        $dateRangeStart = $data['date_range_start'] ?? null;

        if (!empty($ussdSessionId)) {
            $query = UssdSessionFlag::where('app_id', $app->id)->where('ussd_session_id', $ussdSessionId);
        } else {
            $query = UssdSessionFlag::where('app_id', $app->id);
        }

        if (!empty($status))    $query->where('status', $status);
        if (!empty($priority))  $query->where('priority', $priority);

        // Apply Date Range
        $query = $this->applyDateRange($query, $dateRange, $dateRangeStart, $dateRangeEnd);

        // Apply Search
        $query = $this->setQuery($query)->applySearchOnQuery()->getQuery();

        $unfilteredByCategoryQuery = (clone $query);

        if (!empty($category))  $query->where('category', $category);

        //  Total open / resolved
        $aggregates = (clone $query)->selectRaw('
            COUNT(CASE WHEN status = "open" THEN 1 END)                                 as total_open,
            COUNT(CASE WHEN status = "open" AND priority = "critical" THEN 1 END)       as total_critical_open,
            COUNT(CASE WHEN status = "open" AND priority = "high" THEN 1 END)           as total_high_open,
            COUNT(DISTINCT CASE WHEN status = "open" THEN ussd_session_id END)          as total_unique_sessions_with_open_flags,
            COUNT(CASE WHEN status = "resolved" THEN 1 END)                             as total_resolved,
            COUNT(DISTINCT CASE WHEN status = "resolved" THEN ussd_session_id END)      as total_unique_sessions_with_resolved_flags
        ')->first();

        // Get the single most urgent open flag
        $firstUrgentOpenFlag = (clone $query)
            ->where('status', 'open')
            ->orderByRaw("
                CASE priority
                    WHEN 'critical' THEN 1
                    WHEN 'high'     THEN 2
                    WHEN 'medium'   THEN 3
                    WHEN 'low'      THEN 4
                    ELSE 5
                END ASC
            ")                             // critical first (lowest number = highest priority)
            ->latest('created_at')         // newer first within the same priority
            ->first();

        // 1. Open flags category breakdown (only status = 'open')
        $openCategoryBreakdown = (clone $unfilteredByCategoryQuery)
            ->where('status', 'open')
            ->select('category', DB::raw('COUNT(*) as category_count'))
            ->groupBy('category')
            ->orderByDesc('category_count')           // highest count first
            ->pluck('category_count', 'category')
            ->toArray();

        // 2. Resolved flags category breakdown (only status = 'resolved')
        $resolvedCategoryBreakdown = (clone $unfilteredByCategoryQuery)
            ->where('status', 'resolved')
            ->select('category', DB::raw('COUNT(*) as category_count'))
            ->groupBy('category')
            ->orderByDesc('category_count')           // highest count first
            ->pluck('category_count', 'category')
            ->toArray();

        return [
            'total_open'             => (int) $aggregates->total_open,
            'total_resolved'         => (int) $aggregates->total_resolved,
            'total_critical_open'    => (int) $aggregates->total_critical_open,
            'total_high_open'        => (int) $aggregates->total_high_open,
            'total_unique_sessions_with_open_flags'        => (int) $aggregates->total_unique_sessions_with_open_flags,
            'total_unique_sessions_with_resolved_flags'        => (int) $aggregates->total_unique_sessions_with_resolved_flags,
            'open_category_breakdown'           => $openCategoryBreakdown,
            'resolved_category_breakdown'       => $resolvedCategoryBreakdown,

            // Most urgent open flag details
            'most_urgent_open_flag'         => $firstUrgentOpenFlag,
        ];
    }

    /**
     * Show ussd session flag.
     *
     * @param App $app
     * @param UssdSessionFlag $ussdSessionFlag
     * @return UssdSessionFlagResource
     */
    public function showUssdSessionFlag(App $app, UssdSessionFlag $ussdSessionFlag): UssdSessionFlagResource
    {
        return $this->showResource($ussdSessionFlag);
    }

    /**
     * Update ussd session flag.
     *
     * @param App $app
     * @param UssdSessionFlag $ussdSessionFlag
     * @param array $data
     * @return array
     */
    public function updateUssdSessionFlag(App $app, UssdSessionFlag $ussdSessionFlag, array $data): array
    {
        $ussdSessionFlag->update($data);

        return $this->showUpdatedResource($ussdSessionFlag);
    }

    /**
     * Delete ussd session flag.
     *
     * @param App $app
     * @param UssdSessionFlag $ussdSessionFlag
     * @return array
     */
    public function deleteUssdSessionFlag(App $app, UssdSessionFlag $ussdSessionFlag): array
    {
        $deleted = $ussdSessionFlag->delete();

        return [
            'deleted' => $deleted,
            'message' => $deleted ? 'Flag deleted' : 'Flag delete unsuccessful'
        ];
    }

    /**
     * Resolve ussd session flag.
     *
     * @param App $app
     * @param UssdSessionFlag $ussdSessionFlag
     * @param array $data
     * @return array
     */
    public function resolveUssdSessionFlag(App $app, UssdSessionFlag $ussdSessionFlag, array $data): array
    {
        $ussdSessionFlag->update([
            'status' => 'resolved',
            'resolved_at' => now(),
            'resolved_by' => Auth::user()->id,
            'resolution_comment' => $data['resolution_comment'] ?? null,
            'ussd_session_step_id' => $data['ussd_session_step_id'] ?? null
        ]);

        return $this->showUpdatedResource($ussdSessionFlag);
    }

    /**
     * Unresolve ussd session flag.
     *
     * @param App $app
     * @param UssdSessionFlag $ussdSessionFlag
     * @return array
     */
    public function unresolveUssdSessionFlag(App $app, UssdSessionFlag $ussdSessionFlag): array
    {
        $ussdSessionFlag->update([
            'status' => 'open',
            'resolved_at' => null,
            'resolved_by' => null,
            'resolution_comment' => null,
            'ussd_session_step_id' => null,
        ]);

        return $this->showUpdatedResource($ussdSessionFlag);
    }
}
