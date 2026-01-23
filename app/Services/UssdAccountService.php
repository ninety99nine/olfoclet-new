<?php

namespace App\Services;

use App\Enums\Association;
use App\Models\UssdAccount;
use App\Models\UssdSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\UssdAccountResource;
use App\Http\Resources\UssdAccountResources;
use Carbon\Carbon;
use Exception;

class UssdAccountService extends BaseService
{
    /**
     * Show USSD accounts list (paginated).
     *
     * @param array $data
     * @return UssdAccountResources|array
     */
    public function showUssdAccounts(array $data): UssdAccountResources|array
    {
        /** @var User $user */
        $user = Auth::user();

        $appId     = $data['app_id'] ?? null;
        $status    = $data['status'] ?? null;
        $country   = $data['country'] ?? null;
        $network   = $data['network'] ?? null;
        $hasFlags  = $data['has_flags'] ?? null;
        $simulated = $data['simulated'] ?? null;
        $dateRange = $data['date_range'] ?? null;
        $dateRangeEnd = $data['date_range_end'] ?? null;
        $dateRangeStart = $data['date_range_start'] ?? null;
        $association = Association::tryFrom($data['association'] ?? null);

        if ($association === Association::SUPER_ADMIN) {
            $query = UssdAccount::query();
        } elseif (!empty($appId)) {
            $query = UssdAccount::where('app_id', $appId);
        } else {
            $appIds = $user->apps()->pluck('apps.id');
            $query = UssdAccount::whereIn('app_id', $appIds);
        }

        // Apply Filters
        if (!empty($network))           $query->where('network', $network);
        if ($simulated !== null)        $query->where('simulated', $simulated ? 1 : 0);
        if (!empty($country))           $query->where('country', strtoupper($country));
        if ($hasFlags !== null)         $query->where('open_flags_count', $hasFlags ? '>' : '=', 0);

        if ($status === 'active') {
            $query->whereNull('blocked_at')
                  ->where('updated_at', '>=', now()->subDays(30));
        } elseif ($status === 'inactive') {
            $query->whereNull('blocked_at')
                  ->where('updated_at', '<', now()->subDays(30));
        } elseif ($status === 'blocked') {
            $query->whereNotNull('blocked_at');
        }

        $query->addSelect([
            'total_sessions' => UssdSession::selectRaw('COUNT(*)')
                ->whereColumn('ussd_sessions.msisdn', 'ussd_accounts.msisdn'),
        ]);

        $query->addSelect([
            'success_rate' => UssdSession::selectRaw('ROUND(IF(COUNT(*) > 0, (SUM(successful) / COUNT(*)) * 100, 0), 1)')
                ->whereColumn('ussd_sessions.msisdn', 'ussd_accounts.msisdn'),
        ]);

        if($dateRange) {

            // Apply Date Range
            $query = $this->applyDateRange($query, $dateRange, $dateRangeStart, $dateRangeEnd);

        }

        if(!request()->has('_sort')) $query = $query->latest();

        return $this->setQuery($query)->getOutput();
    }

    /**
     * Show summary statistics for USSD accounts.
     *
     * @param array $data
     * @return array
     */
    public function showUssdAccountsSummary(array $data): array
    {
        /** @var User $user */
        $user = Auth::user();

        $appId     = $data['app_id'] ?? null;
        $status    = $data['status'] ?? null;
        $country   = $data['country'] ?? null;
        $network   = $data['network'] ?? null;
        $hasFlags  = $data['has_flags'] ?? null;
        $simulated = $data['simulated'] ?? null;
        $dateRange = $data['date_range'] ?? null;
        $dateRangeEnd = $data['date_range_end'] ?? null;
        $dateRangeStart = $data['date_range_start'] ?? null;
        $association = Association::tryFrom($data['association'] ?? null);

        if ($association === Association::SUPER_ADMIN) {
            $query = UssdAccount::query();
        } elseif (!empty($appId)) {
            $query = UssdAccount::where('app_id', $appId);
        } else {
            $appIds = $user->apps()->pluck('apps.id');
            $query = UssdAccount::whereIn('app_id', $appIds);
        }

        // Apply Filters
        if (!empty($network))           $query->where('network', $network);
        if ($simulated !== null)        $query->where('simulated', $simulated ? 1 : 0);
        if (!empty($country))           $query->where('country', strtoupper($country));
        if ($hasFlags !== null)         $query->where('open_flags_count', $hasFlags ? '>' : '=', 0);

        if ($status === 'active') {
            $query->whereNull('blocked_at')
                  ->where('updated_at', '>=', now()->subDays(30));
        } elseif ($status === 'inactive') {
            $query->whereNull('blocked_at')
                  ->where('updated_at', '<', now()->subDays(30));
        } elseif ($status === 'blocked') {
            $query->whereNotNull('blocked_at');
        }

        // Apply Date Range
        $query = $this->applyDateRange($query, $dateRange, $dateRangeStart, $dateRangeEnd);

        // Apply Search
        $query = $this->setQuery($query)->applySearchOnQuery()->getQuery();

        $totalAccounts = (clone $query)->count();

        $now = now();
        $currentStart = null;
        $currentEnd   = $now;
        $periodLabel  = 'This month';

        if ($dateRangeStart && $dateRangeEnd) {

            // Custom range
            try {
                $currentStart = Carbon::parse($dateRangeStart)->startOfDay();
                $currentEnd   = Carbon::parse($dateRangeEnd)->endOfDay();
                $periodLabel  = 'This period';
            } catch (\Exception $e) {

            }
        } elseif ($dateRange) {
            // Preset ranges
            switch ($dateRange) {
                case 'today':
                    $currentStart = $now->clone()->startOfDay();
                    $periodLabel  = 'Today';
                    break;

                case 'this_week':
                    $currentStart = $now->clone()->startOfWeek()->startOfDay();
                    $periodLabel  = 'This week';
                    break;

                case 'this_month':
                    $currentStart = $now->clone()->startOfMonth()->startOfDay();
                    $periodLabel  = 'This month';
                    break;

                case 'this_year':
                    $currentStart = $now->clone()->startOfYear()->startOfDay();
                    $periodLabel  = 'This year';
                    break;

                default:
                    $currentStart = $now->clone()->startOfMonth()->startOfDay();
                    break;
            }
        }

        // Fallback if no range specified
        if (!$currentStart) {
            $currentStart = $now->clone()->startOfMonth()->startOfDay();
            $periodLabel  = 'This month';
        }

        // Count new accounts in the CURRENT period
        $newThisPeriod = (clone $query)
            ->where('created_at', '>=', $currentStart)
            ->where('created_at', '<=', $currentEnd)
            ->count();

        $newDisplay = $newThisPeriod >= 0 ? "+$newThisPeriod" : (string) $newThisPeriod;

        $activeCount = (clone $query)
            ->whereNull('blocked_at')
            ->where('updated_at', '>=', now()->subDays(30))
            ->count();

        $activePercentage = $totalAccounts > 0 ? round(($activeCount / $totalAccounts) * 100, 1) : 0;

        $avgSessions = (clone $query)->avg(DB::raw('(
            SELECT COUNT(*)
            FROM ussd_sessions
            WHERE ussd_sessions.msisdn = ussd_accounts.msisdn
        )')) ?? 0;

        $msisdns = (clone $query)->pluck('msisdn');
        $avgDurationMs = 0;

        if ($msisdns->isNotEmpty()) {
            $totalDuration = UssdSession::whereIn('msisdn', $msisdns)
                ->sum('total_duration_ms');
            $totalSessions = UssdSession::whereIn('msisdn', $msisdns)->count();
            $avgDurationMs = $totalSessions > 0 ? round($totalDuration / $totalSessions) : 0;
        }

        $networkStats = (clone $query)
            ->select('network', DB::raw('COUNT(*) as count'))
            ->groupBy('network')
            ->get()
            ->mapWithKeys(function ($item) use ($totalAccounts) {
                $percentage = $totalAccounts > 0 ? round(($item->count / $totalAccounts) * 100, 1) : 0;
                return [($item->network ?? 'Unknown') => $percentage];
            })
            ->toArray();

        return [
            'total_accounts'           => $totalAccounts,
            'new_this_period'          => $newThisPeriod,
            'new_this_period_display'  => $newDisplay,
            'period_label'             => $periodLabel,          // "This week", "Today", etc.
            'active_accounts'          => $activeCount,
            'active_percentage'        => $activePercentage,
            'avg_sessions'             => round($avgSessions),
            'avg_session_duration_ms'  => (int) $avgDurationMs,
            'network_breakdown'        => $networkStats,
        ];
    }

    /**
     * Show USSD account.
     *
     * @param UssdAccount $ussdAccount
     * @return UssdAccountResource
     */
    public function showUssdAccount(UssdAccount $ussdAccount): UssdAccountResource
    {
        return $this->showResource($ussdAccount);
    }

    /**
     * Block USSD accounts.
     *
     * @param array $accountIds
     * @return array
     * @throws Exception
     */
    public function blockUssdAccounts(array $accountIds): array
    {
        $accounts = UssdAccount::whereIn('id', $accountIds)->get();

        if ($total = $accounts->count()) {
            foreach ($accounts as $account) {
                $this->blockUssdAccount($account);
            }

            return [
                'message' => $total . ($total === 1 ? ' account' : ' accounts') . ' blocked'
            ];
        }

        throw new Exception('No accounts blocked');
    }

    /**
     * Block USSD account.
     *
     * @param UssdAccount $ussdAccount
     * @return array
     */
    public function blockUssdAccount(UssdAccount $ussdAccount): array
    {
        $alreadyBlocked = $ussdAccount->blocked_at !== null;

        if (!$alreadyBlocked) {
            $ussdAccount->update(['blocked_at' => now()]);
        }

        return [
            'blocked' => true,
            'message' => $alreadyBlocked ? 'Account already blocked' : 'Account blocked'
        ];
    }

    /**
     * Unblock USSD account.
     *
     * @param UssdAccount $ussdAccount
     * @return array
     */
    public function unblockUssdAccount(UssdAccount $ussdAccount): array
    {
        $alreadyUnblocked = $ussdAccount->blocked_at == null;

        if (!$alreadyUnblocked) {
            $ussdAccount->update(['blocked_at' => null]);
        }

        return [
            'blocked' => true,
            'message' => $alreadyUnblocked ? 'Account already unblocked' : 'Account unblocked'
        ];
    }

    /**
     * Delete USSD accounts.
     *
     * @param array $accountIds
     * @return array
     * @throws Exception
     */
    public function deleteUssdAccounts(array $accountIds): array
    {
        $accounts = UssdAccount::whereIn('id', $accountIds)->get();

        if ($total = $accounts->count()) {
            foreach ($accounts as $account) {
                $this->deleteUssdAccount($account);
            }

            return [
                'message' => $total . ($total === 1 ? ' account' : ' accounts') . ' deleted'
            ];
        }

        throw new Exception('No accounts deleted');
    }

    /**
     * Delete USSD account.
     *
     * @param UssdAccount $ussdAccount
     * @return array
     */
    public function deleteUssdAccount(UssdAccount $ussdAccount): array
    {
        $deleted = $ussdAccount->delete();

        return [
            'deleted' => $deleted,
            'message' => $deleted ? 'Account deleted' : 'Account delete unsuccessful'
        ];
    }

    /**
     * Show USSD account summary (only fields used in frontend cards).
     *
     * @param UssdAccount $ussdAccount
     * @return array
     */
    public function showUssdAccountSummary(UssdAccount $ussdAccount): array
    {
        $stats = UssdSession::where('ussd_account_id', $ussdAccount->id)
            ->selectRaw('
                COUNT(*) as total_sessions,
                SUM(CASE WHEN successful = 1 THEN 1 ELSE 0 END) as successful_sessions,
                SUM(CASE WHEN simulated = 0 THEN 1 ELSE 0 END) as mobile_sessions,
                SUM(CASE WHEN simulated = 1 THEN 1 ELSE 0 END) as simulator_sessions,
                SUM(total_duration_ms) as total_duration_ms,
                SUM(total_steps) as total_steps,
                MIN(created_at) as first_seen,
                MAX(updated_at) as last_activity
            ')
            ->first();

        $totalSessions      = (int) ($stats->total_sessions ?? 0);
        $successfulSessions = (int) ($stats->successful_sessions ?? 0);
        $mobileSessions     = (int) ($stats->mobile_sessions ?? 0);
        $totalDurationMs    = (int) ($stats->total_duration_ms ?? 0);
        $totalSteps         = (int) ($stats->total_steps ?? 0);

        $successRate = $totalSessions > 0 ? round(($successfulSessions / $totalSessions) * 100, 1) : 0;

        $mobilePercentage    = $totalSessions > 0 ? round(($mobileSessions / $totalSessions) * 100, 1) : 0;
        $simulatorPercentage = 100 - $mobilePercentage;

        $avgDurationMs = $totalSessions > 0 ? round($totalDurationMs / $totalSessions) : 0;
        $avgSteps      = $totalSessions > 0 ? round($totalSteps / $totalSessions, 1) : 0;

        $firstSeen = $stats->first_seen
            ? Carbon::parse($stats->first_seen)->format('d M Y')
            : '—';

        $lastActivity = $stats->last_activity
            ? Carbon::parse($stats->last_activity)->format('d M Y')
            : ($firstSeen !== '—' ? $firstSeen : '—');

        return [
            'total_sessions' => $totalSessions,
            'success_rate'   => $successRate,

            'first_seen'     => $firstSeen,
            'last_activity'  => $lastActivity,

            'avg_duration_ms'   => $avgDurationMs,
            'avg_steps'      => $avgSteps,

            'mobile_percentage'    => $mobilePercentage,
            'simulator_percentage' => $simulatorPercentage,
        ];
    }
}
