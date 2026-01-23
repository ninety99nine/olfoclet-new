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

        $status        = $data['status'] ?? null;
        $priority      = $data['priority'] ?? null;
        $category      = $data['category'] ?? null;
        $ussdSessionId = $data['ussd_session_id'] ?? null;
        $association   = isset($data['association']) ? Association::tryFrom($data['association']) : null;

        if ($association === Association::SUPER_ADMIN) {
            $query = UssdSessionFlag::query()->latest();
        } else if (!empty($ussdSessionId)) {
            $query = UssdSessionFlag::where('ussd_session_id', $ussdSessionId);
        } else {
            $appIds = $user->apps()->pluck('apps.id');
            $query = UssdSessionFlag::whereIn('ussd_session_id', function ($q) use ($appIds) {
                $q->select('id')
                  ->from('ussd_sessions')
                  ->whereIn('app_id', $appIds);
            });
        }

        if (!empty($status))    $query->where('status', $status);
        if (!empty($category))  $query->where('category', $category);
        if (!empty($priority))  $query->where('priority', $priority);

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

        $status        = $data['status'] ?? null;
        $priority      = $data['priority'] ?? null;
        $category      = $data['category'] ?? null;
        $ussdSessionId = $data['ussd_session_id'] ?? null;
        $association   = isset($data['association']) ? Association::tryFrom($data['association']) : null;

        if ($association === Association::SUPER_ADMIN) {
            $query = UssdSessionFlag::query()->latest();
        } else if (!empty($ussdSessionId)) {
            $query = UssdSessionFlag::where('ussd_session_id', $ussdSessionId);
        } else {
            $appIds = $user->apps()->pluck('apps.id');
            $query = UssdSessionFlag::whereIn('ussd_session_id', function ($q) use ($appIds) {
                $q->select('id')
                  ->from('ussd_sessions')
                  ->whereIn('app_id', $appIds);
            });
        }

        if (!empty($status))    $query->where('status', $status);
        if (!empty($category))  $query->where('category', $category);
        if (!empty($priority))  $query->where('priority', $priority);

        // ── Core aggregates ──
        $totalOpen     = (clone $query)->where('status', 'open')->count();
        $totalResolved = (clone $query)->where('status', 'resolved')->count();

        $criticalOpen = (clone $query)
            ->where('status', 'open')
            ->where('priority', 'critical')
            ->count();

        $highOpen = (clone $query)
            ->where('status', 'open')
            ->where('priority', 'high')
            ->count();

        // Category breakdown (only open flags for simplicity)
        $categoryBreakdown = (clone $query)
            ->where('status', 'open')
            ->select('category', DB::raw('count(*) as count'))
            ->groupBy('category')
            ->pluck('count', 'category')
            ->toArray();

        // Hot sessions = sessions with most open flags
        $topSessions = (clone $query)
            ->where('status', 'open')
            ->select('ussd_session_id', DB::raw('count(*) as open_count'))
            ->groupBy('ussd_session_id')
            ->orderByDesc('open_count')
            ->limit(5)
            ->get()
            ->map(function ($row) {
                return [
                    'ussd_session_id' => $row->ussd_session_id,
                    'count'           => $row->open_count,
                ];
            })
            ->toArray();

        // Unique sessions with at least one open flag
        $uniqueSessionsWithOpen = (clone $query)
            ->where('status', 'open')
            ->distinct('ussd_session_id')
            ->count('ussd_session_id');

        return [
            'total_open'          => $totalOpen,
            'resolved'            => $totalResolved,
            'critical_open'       => $criticalOpen,
            'high_open'           => $highOpen,
            'unique_sessions'     => $uniqueSessionsWithOpen,
            'category_breakdown'  => $categoryBreakdown,     // ['bug' => 7, 'performance' => 4, ...]
            'top_sessions'        => $topSessions,           // array of {ussd_session_id, count}
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
