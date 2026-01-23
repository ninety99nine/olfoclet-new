<?php

namespace App\Services;

use App\Enums\Association;
use App\Models\UssdSessionFlag;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\UssdSessionFlagResource;
use App\Http\Resources\UssdSessionFlagResources;
use Illuminate\Support\Facades\DB;

class UssdSessionFlagService extends BaseService
{
    /**
     * Show USSD session flags.
     *
     * @param array $data
     * @return UssdSessionFlagResources|array
     */
    public function showUssdSessionFlags(array $data): UssdSessionFlagResources|array
    {
        /** @var User $user */
        $user = Auth::user();

        $appId          = $data['app_id'] ?? null;
        $status         = $data['status'] ?? null;
        $priority       = $data['priority'] ?? null;
        $category       = $data['category'] ?? null;
        $dateRange      = $data['date_range'] ?? null;
        $dateRangeEnd   = $data['date_range_end'] ?? null;
        $ussdSessionId  = $data['ussd_session_id'] ?? null;
        $dateRangeStart = $data['date_range_start'] ?? null;
        $association   = isset($data['association']) ? Association::tryFrom($data['association']) : null;

        if ($association === Association::SUPER_ADMIN) {
            $query = UssdSessionFlag::query()->latest();
        } else if (!empty($ussdSessionId)) {
            $query = UssdSessionFlag::where('ussd_session_id', $ussdSessionId);
        } else if (!empty($appId)) {
            $query = UssdSessionFlag::where('app_id', $appId);
        } else {
            $appIds = $user->apps()->pluck('apps.id');
            $query = UssdSessionFlag::whereIn('app_id', $appIds);
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
     * Create USSD session flag.
     *
     * @param array $data
     * @return array
     */
    public function createUssdSessionFlag(array $data): array
    {
        $data = [
            ...$data,
            'created_by' => Auth::user()->id
        ];

        $flag = UssdSessionFlag::create($data);

        return $this->showCreatedResource($flag);
    }

    /**
     * Get aggregated summary statistics for flags
     *
     * @param array $data
     * @return array
     */
    public function showUssdSessionFlagsSummary(array $data): array
    {
        /** @var User $user */
        $user = Auth::user();

        $appId          = $data['app_id'] ?? null;
        $status         = $data['status'] ?? null;
        $priority       = $data['priority'] ?? null;
        $category       = $data['category'] ?? null;
        $dateRange      = $data['date_range'] ?? null;
        $dateRangeEnd   = $data['date_range_end'] ?? null;
        $ussdSessionId  = $data['ussd_session_id'] ?? null;
        $dateRangeStart = $data['date_range_start'] ?? null;
        $association   = isset($data['association']) ? Association::tryFrom($data['association']) : null;

        if ($association === Association::SUPER_ADMIN) {
            $query = UssdSessionFlag::query()->latest();
        } else if (!empty($ussdSessionId)) {
            $query = UssdSessionFlag::where('ussd_session_id', $ussdSessionId);
        } else if (!empty($appId)) {
            $query = UssdSessionFlag::where('app_id', $appId);
        } else {
            $appIds = $user->apps()->pluck('apps.id');
            $query = UssdSessionFlag::whereIn('app_id', $appIds);
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
            COUNT(CASE WHEN status = "open" THEN 1 END)                    as total_open,
            COUNT(CASE WHEN status = "open" AND priority = "critical" THEN 1 END) as total_critical_open,
            COUNT(CASE WHEN status = "open" AND priority = "high" THEN 1 END)     as total_high_open,
            COUNT(DISTINCT CASE WHEN status = "open" THEN ussd_session_id END)    as total_unique_sessions_with_open_flags,
            COUNT(CASE WHEN status = "resolved" THEN 1 END)                as total_resolved
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
            'open_category_breakdown'           => $openCategoryBreakdown,
            'resolved_category_breakdown'       => $resolvedCategoryBreakdown,

            // Most urgent open flag details
            'first_urgent_open_flag'         => $firstUrgentOpenFlag,
        ];
    }

    /**
     * Show USSD session flag.
     *
     * @param UssdSessionFlag $ussdSessionFlag
     * @return UssdSessionFlagResource
     */
    public function showUssdSessionFlag(UssdSessionFlag $ussdSessionFlag): UssdSessionFlagResource
    {
        return $this->showResource($ussdSessionFlag);
    }

    /**
     * Update USSD session flag.
     *
     * @param UssdSessionFlag $ussdSessionFlag
     * @param array $data
     * @return array
     */
    public function updateUssdSessionFlag(UssdSessionFlag $ussdSessionFlag, array $data): array
    {
        $ussdSessionFlag->update($data);

        return $this->showUpdatedResource($ussdSessionFlag);
    }

    /**
     * Delete USSD session flag.
     *
     * @param UssdSessionFlag $ussdSessionFlag
     * @return array
     */
    public function deleteUssdSessionFlag(UssdSessionFlag $ussdSessionFlag): array
    {
        $deleted = $ussdSessionFlag->delete();

        return [
            'deleted' => $deleted,
            'message' => $deleted ? 'Flag deleted' : 'Flag delete unsuccessful'
        ];
    }

    /**
     * Resolve USSD session flag.
     *
     * @param UssdSessionFlag $ussdSessionFlag
     * @param array $data
     * @return array
     */
    public function resolveUssdSessionFlag(UssdSessionFlag $ussdSessionFlag, array $data): array
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
     * Unresolve USSD session flag.
     *
     * @param UssdSessionFlag $ussdSessionFlag
     * @return array
     */
    public function unresolveUssdSessionFlag(UssdSessionFlag $ussdSessionFlag): array
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
