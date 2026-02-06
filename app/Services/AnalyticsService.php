<?php

namespace App\Services;

use App\Models\UssdSession;
use App\Models\UssdAccount;
use App\Models\UssdSessionFlag;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class AnalyticsService
{
    public function getOverview(array $filters): array
    {
        $period = $this->getPeriod($filters);
        $querySessions = $this->baseSessionQuery($filters);
        $queryAccounts = $this->baseAccountQuery($filters);

        // Total sessions
        $totalSessions = (clone $querySessions)->whereBetween('created_at', [$period['start'], $period['end']])->count();

        // Success rate
        $successful = (clone $querySessions)
            ->where('successful', true)
            ->whereBetween('created_at', [$period['start'], $period['end']])->count();

        $successRate = $totalSessions > 0 ? round($successful / $totalSessions * 100, 1) : 0;

        // Avg duration
        $avgDurationMs = (clone $querySessions)
            ->whereBetween('created_at', [$period['start'], $period['end']])
            ->avg('total_duration_ms') ?? 0;

        // New users this period
        $newUsers = (clone $queryAccounts)->whereBetween('created_at', [$period['start'], $period['end']])->count();

        // Return users this period
        $returnUsers = $this->baseAccountQuery($filters)
            ->join('ussd_sessions', 'ussd_accounts.id', '=', 'ussd_sessions.ussd_account_id')
            ->where('ussd_accounts.created_at', '<', $period['start'])
            ->whereBetween('ussd_sessions.created_at', [$period['start'], $period['end']])
            ->distinct()
            ->count('ussd_accounts.id');

        // Lapsed users this period
        $lapsedUsers = $this->baseAccountQuery($filters)
            ->where('ussd_accounts.created_at', '<', $period['start'])              // existed before period
            ->whereDoesntHave('sessions', function ($query) use ($period) {         // NO sessions in period
                $query->whereBetween('ussd_sessions.created_at', [
                    $period['start'],
                    $period['end']
                ]);
            })
            ->count();

        $activeUsers = $newUsers + $returnUsers;
        $totalUsers = $newUsers + $returnUsers + $lapsedUsers;

        $newUsersPercentage = $totalUsers == 0 ? 0 : round($newUsers/$totalUsers * 100);
        $returnUsersPercentage = $totalUsers == 0 ? 0 : round($returnUsers/$totalUsers * 100);
        $lapsedUsersPercentage = $totalUsers == 0 ? 0 : round($lapsedUsers/$totalUsers * 100);

        // Sessions WITH at least one OPEN flag (status = 'open')
        $sessionsWithOpenFlags = (clone $querySessions)
            ->whereBetween('created_at', [$period['start'], $period['end']])
            ->whereHas('flags', function ($q) {
                $q->where('status', 'open');
            })
            ->count();

        // Sessions WITH at least one RESOLVED flag (status = 'resolved')
        $sessionsWithResolvedFlags = (clone $querySessions)
            ->whereBetween('created_at', [$period['start'], $period['end']])
            ->whereHas('flags', function ($q) {
                $q->where('status', 'resolved');
            })
            ->count();

        return [
            'total_sessions'            => number_format($totalSessions),
            'success_rate'              => $successRate,
            'avg_duration_ms'           => $avgDurationMs,
            'new_users'                 => number_format($newUsers),
            'total_users'               => number_format($totalUsers),
            'return_users'              => number_format($returnUsers),
            'lapsed_users'              => number_format($lapsedUsers),

            'new_users_percentage'      => $newUsersPercentage,
            'return_users_percentage'   => $returnUsersPercentage,
            'lapsed_users_percentage'   => $lapsedUsersPercentage,

            'open_flags'         => $sessionsWithOpenFlags,
            'resolved_flags'     => $sessionsWithResolvedFlags
        ];
    }

    // ── Time series ────────────────────────────────────────────────────────

    public function getSessionsOverTime(array $filters): array
    {
        $period = $this->getPeriod($filters);
        $start = $period['start'];
        $end = $period['end'];

        $isToday = $start->isSameDay($end) && $start->startOfDay()->eq($start) && $end->endOfDay()->eq($end);

        if ($isToday) {
            // Hourly grouping for "today"
            $data = $this->baseSessionQuery($filters)
                ->selectRaw("HOUR(created_at) as hour, COUNT(*) as count, SUM(successful) as successful")
                ->whereBetween('created_at', [$start, $end])
                ->groupBy('hour')
                ->orderBy('hour')
                ->get();

            $filled = $this->fillMissingHours(
                $data->toArray(),
                0,
                23,
                ['count', 'successful']
            );

            $successRates = [];
            foreach ($filled['count'] as $index => $count) {
                $successful = $filled['successful'][$index];
                // If no sessions → 100% success (no failures)
                $successRates[] = $count > 0 ? round($successful / $count * 100, 1) : 100;
            }

            return [
                'labels'       => $filled['labels'],
                'sessions'     => $filled['count'],
                'success_rate' => $successRates,
            ];
        }

        // Daily grouping for all other ranges
        $data = $this->baseSessionQuery($filters)
            ->selectRaw("DATE(created_at) as date, COUNT(*) as count, SUM(successful) as successful")
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $filled = $this->fillMissingDays(
            $data->toArray(),
            $start,
            $end,
            ['count', 'successful']
        );

        $successRates = [];
        foreach ($filled['count'] as $index => $count) {
            $successful = $filled['successful'][$index];
            // If no sessions → 100% success (no failures)
            $successRates[] = $count > 0 ? round($successful / $count * 100, 1) : 100;
        }

        return [
            'labels'       => $filled['labels'],
            'sessions'     => $filled['count'],
            'success_rate' => $successRates,
        ];
    }

    public function getNewUsersOverTime(array $filters): array
    {
        $period = $this->getPeriod($filters);
        $start = $period['start'];
        $end = $period['end'];

        $isToday = $filters['range'] == 'today';

        if ($isToday) {

            // Hourly for today
            $data = $this->baseAccountQuery($filters)
                ->selectRaw("HOUR(created_at) as hour, COUNT(*) as count")
                ->whereBetween('created_at', [$start, $end])
                ->groupBy('hour')
                ->orderBy('hour')
                ->get();

            $filled = $this->fillMissingHours(
                $data->toArray(),
                0,
                23,
                ['count']
            );

            return [
                'labels' => $filled['labels'],
                'values' => $filled['count'],
            ];

        }

        // Daily for other ranges
        $data = $this->baseAccountQuery($filters)
            ->selectRaw("DATE(created_at) as date, COUNT(*) as count")
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $filled = $this->fillMissingDays(
            $data->toArray(),
            $start,
            $end,
            ['count']
        );

        return [
            'labels' => $filled['labels'],
            'values' => $filled['count'],
        ];
    }

    public function getReturnUsersOverTime(array $filters): array
    {
        $period = $this->getPeriod($filters);
        $start = $period['start'];
        $end = $period['end'];

        $isToday = $filters['range'] === 'today';

        if ($isToday) {

            // Hourly for today — counting each returning user only ONCE at their LAST active hour
            $data = $this->baseAccountQuery($filters)
                ->join('ussd_sessions', 'ussd_accounts.id', '=', 'ussd_sessions.ussd_account_id')
                ->where('ussd_accounts.created_at', '<', $start) // Existed before today
                ->whereBetween('ussd_sessions.created_at', [$start, $end])
                // Find the maximum (latest) hour for each unique account
                ->selectRaw("MAX(HOUR(ussd_sessions.created_at)) as hour")
                ->groupBy('ussd_accounts.id')
                ->get()
                // Group the results by the hour and count the users in each
                ->groupBy('hour')
                ->map(function ($group, $hour) {
                    return [
                        'hour' => $hour,
                        'count' => $group->count()
                    ];
                })
                ->values()
                ->toArray();

            $filled = $this->fillMissingHours(
                $data,
                0,
                23,
                ['count']
            );

            return [
                'labels' => $filled['labels'],
                'values' => $filled['count'],
            ];
        }

        // Daily for other ranges — using the same whereHas logic
        $data = $this->baseAccountQuery($filters)
            ->join('ussd_sessions', 'ussd_accounts.id', '=', 'ussd_sessions.ussd_account_id')
            ->where('ussd_accounts.created_at', '<', $period['start'])
            ->whereBetween('ussd_sessions.created_at', [$period['start'], $period['end']])
            // Select the latest session date for each unique user
            ->selectRaw("MAX(DATE(ussd_sessions.created_at)) as date")
            ->groupBy('ussd_accounts.id')
            ->get()
            // At this point we have a list of dates (one per user).
            // Now we count how many users fall into each date group.
            ->groupBy('date')
            ->map(function ($group, $date) {
                return [
                    'date' => $date,
                    'count' => $group->count()
                ];
            })
            ->values()
            ->toArray();

        $filled = $this->fillMissingDays(
            $data,
            $start,
            $end,
            ['count']
        );

        return [
            'labels' => $filled['labels'],
            'values' => $filled['count'],
        ];
    }

    // ── Breakdowns (non-time-series) ──────────────────────────────────────

    public function getByCountry(array $filters): array
    {
        $period = $this->getPeriod($filters);

        return $this->baseSessionQuery($filters)
            ->select('country', DB::raw('COUNT(*) as count'))
            ->whereBetween('created_at', [$period['start'], $period['end']])
            ->groupBy('country')
            ->orderByDesc('count')
            ->get()
            ->map(fn($row) => [
                'country'  => $row->country ?: 'Unknown',
                'sessions' => $row->count,
            ])
            ->toArray();
    }

    public function getByNetwork(array $filters): array
    {
        $period = $this->getPeriod($filters);

        return $this->baseSessionQuery($filters)
            ->select('network', DB::raw('COUNT(*) as count'))
            ->whereBetween('created_at', [$period['start'], $period['end']])
            ->groupBy('network')
            ->orderByDesc('count')
            ->get()
            ->map(fn($row) => [
                'network' => $row->network ?: 'Unknown',
                'sessions' => $row->count,
            ])
            ->toArray();
    }

    public function getByNetworkAndCountry(array $filters): array
    {
        $period = $this->getPeriod($filters);

        return $this->baseSessionQuery($filters)
            ->select('network', 'country', DB::raw('COUNT(*) as count'))
            ->whereBetween('created_at', [$period['start'], $period['end']])
            ->groupBy('network', 'country')
            ->orderByDesc('count')
            ->get()
            ->groupBy('network')
            ->map(function ($group, $network) {
                return [
                    'network' => $network ?: 'Unknown',
                    'countries' => $group->map(fn($row) => [
                        'country'  => $row->country ?: 'Unknown',
                        'sessions' => $row->count,
                    ])->values()->toArray()
                ];
            })
            ->values()
            ->toArray();
    }

    public function getByDevice(array $filters): array
    {
        $period = $this->getPeriod($filters);

        return $this->baseSessionQuery($filters)
            ->selectRaw("
                CASE
                    WHEN simulated = 1 THEN 'Simulator'
                    ELSE 'Mobile'
                END as device,
                COUNT(*) as count
            ")
            ->whereBetween('created_at', [$period['start'], $period['end']])
            ->groupBy('device')
            ->orderByDesc('count')
            ->get()
            ->map(fn($row) => [
                'device'   => $row->device,
                'sessions' => $row->count,
            ])
            ->toArray();
    }

    public function getPeakHoursHeatmap(array $filters): array
    {
        $period = $this->getPeriod($filters);

        $data = $this->baseSessionQuery($filters)
            ->selectRaw("
                DAYOFWEEK(created_at) - 1 as day_of_week,
                HOUR(created_at) as hour,
                COUNT(*) as sessions
            ")
            ->whereBetween('created_at', [$period['start'], $period['end']])
            ->groupBy('day_of_week', 'hour')
            ->orderBy('day_of_week')
            ->orderBy('hour')
            ->get();

        $matrix = array_fill(0, 24, array_fill(0, 7, 0));

        foreach ($data as $row) {
            $matrix[$row->hour][$row->day_of_week] = $row->sessions;
        }

        return [
            'matrix' => $matrix,
            'max'    => $data->max('sessions') ?? 1,
            'days'   => ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']
        ];
    }

    // ── Flags ──────────────────────────────────────────────────────────────

    public function getFlagsStatus(array $filters): array
    {
        $period = $this->getPeriod($filters);

        // Sessions with at least one open flag
        $sessionsWithOpen = $this->baseSessionQuery($filters)
            ->whereBetween('created_at', [$period['start'], $period['end']])
            ->whereHas('flags', fn($q) => $q->where('status', 'open'))
            ->count();

        // Sessions with at least one resolved flag
        $sessionsWithResolved = $this->baseSessionQuery($filters)
            ->whereBetween('created_at', [$period['start'], $period['end']])
            ->whereHas('flags', fn($q) => $q->where('status', 'resolved'))
            ->count();

        return [
            'open'     => $sessionsWithOpen,
            'resolved' => $sessionsWithResolved,
            'total' => $sessionsWithOpen + $sessionsWithResolved,
        ];
    }

    public function getFlagsByPriority(array $filters): array
    {
        $period = $this->getPeriod($filters);

        return DB::table('ussd_session_flags')
            ->where('ussd_session_flags.status', 'open')
            ->whereBetween('ussd_session_flags.created_at', [$period['start'], $period['end']])
            ->join('ussd_sessions', 'ussd_session_flags.ussd_session_id', '=', 'ussd_sessions.id')
            ->select('priority', DB::raw('COUNT(DISTINCT ussd_sessions.id) as session_count'))
            ->groupBy('priority')
            ->orderByDesc('session_count')
            ->get()
            ->map(fn($row) => [
                'priority' => ucfirst($row->priority ?: 'Unknown'),
                'count'    => $row->session_count,
            ])
            ->toArray();
    }

    public function getFlagsByCategory(array $filters): array
    {
        $period = $this->getPeriod($filters);

        return DB::table('ussd_session_flags')
            ->where('ussd_session_flags.status', 'open')
            ->whereBetween('ussd_session_flags.created_at', [$period['start'], $period['end']])
            ->join('ussd_sessions', 'ussd_session_flags.ussd_session_id', '=', 'ussd_sessions.id')
            ->select('category', DB::raw('COUNT(DISTINCT ussd_sessions.id) as session_count'))
            ->groupBy('category')
            ->orderByDesc('session_count')
            ->get()
            ->map(fn($row) => [
                'category' => ucfirst($row->category ?: 'Unknown'),
                'count'    => $row->session_count,
            ])
            ->toArray();
    }

    public function getFailedSessionsOverTime(array $filters): array
    {
        $period = $this->getPeriod($filters);
        $start = $period['start'];
        $end = $period['end'];

        $isToday = $filters['range'] === 'today';

        if ($isToday) {
            // Hourly for today
            $data = $this->baseSessionQuery($filters)
                ->selectRaw("HOUR(created_at) as hour, COUNT(*) as count")
                ->whereBetween('created_at', [$start, $end])
                ->where('successful', false)  // ← only failed sessions
                ->groupBy('hour')
                ->orderBy('hour')
                ->get();

            $filled = $this->fillMissingHours(
                $data->toArray(),
                0,
                23,
                ['count']
            );

            return [
                'labels' => $filled['labels'],
                'values' => $filled['count'],
            ];
        }

        // Daily for other ranges
        $data = $this->baseSessionQuery($filters)
            ->selectRaw("DATE(created_at) as date, COUNT(*) as count")
            ->whereBetween('created_at', [$start, $end])
            ->where('successful', false)  // ← only failed sessions
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $filled = $this->fillMissingDays(
            $data->toArray(),
            $start,
            $end,
            ['count']
        );

        return [
            'labels' => $filled['labels'],
            'values' => $filled['count'],
        ];
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    protected function baseSessionQuery(array $filters)
    {
        $query = UssdSession::query();

        if (!empty($filters['app_id'])) {
            $query->where('ussd_sessions.app_id', $filters['app_id']);
        }

        return $query;
    }

    protected function baseAccountQuery(array $filters)
    {
        $query = UssdAccount::query();

        if (!empty($filters['app_id'])) {
            $query->where('ussd_accounts.app_id', $filters['app_id']);
        }

        return $query;
    }

    protected function getPeriod(array $filters): array
    {
        $end   = now();
        $range = $filters['range'] ?? '30d';

        if ($range === 'today')     $start = $end->copy()->startOfDay();
        else if ($range === '7d')   $start = $end->copy()->subDays(6)->startOfDay();    //  7 days (Today + 6 previous)
        else if ($range === '30d')  $start = $end->copy()->subDays(29)->startOfDay();   //  30 days (Today + 29 previous)
        else if ($range === '90d')  $start = $end->copy()->subDays(89)->startOfDay();   //  90 days (Today + 89 previous)
        else if ($range === 'ytd')  $start = $end->copy()->startOfYear();               //  Jan 1st to Today
        else if ($range === 'custom' && !empty($filters['start']) && !empty($filters['end'])) {
            $start = Carbon::parse($filters['start']);
            $end   = Carbon::parse($filters['end'])->endOfDay();
        } else {
            $start = $end->copy()->subDays(30); // fallback
        }

        return compact('start', 'end');
    }

    /**
     * Fill missing days with zero values in a time-series result.
     */
    protected function fillMissingDays($rawData, Carbon $start, Carbon $end, array $fields = ['count']): array
    {
        $data = collect($rawData)->map(fn($item) => is_object($item) ? (array)$item : $item)->toArray();

        $dataByDate = [];
        foreach ($data as $row) {
            $dateValue = $row['date'] ?? null;
            $normalized = $dateValue instanceof Carbon
                ? $dateValue->format('Y-m-d')
                : (is_string($dateValue) ? Carbon::parse($dateValue)->format('Y-m-d') : null);

            if ($normalized) {
                $dataByDate[$normalized] = (object)$row;
            }
        }

        $dateRange = CarbonPeriod::create($start->startOfDay(), $end->endOfDay())->toArray();
        $allDates = collect($dateRange)->map(fn($d) => $d->format('Y-m-d'))->toArray();

        $result = ['labels' => []];
        foreach ($fields as $field) {
            $result[$field] = [];
        }

        foreach ($allDates as $dateStr) {
            $result['labels'][] = Carbon::parse($dateStr)->format('M j');

            $row = $dataByDate[$dateStr] ?? null;

            foreach ($fields as $field) {
                $value = 0;
                if ($row) {
                    $value = $row->$field ?? 0;
                }
                $result[$field][] = $value;
            }
        }

        return $result;
    }

    /**
     * Fill missing hours (0–23) with zero values for a single day.
     */
    protected function fillMissingHours(array $data, int $startHour, int $endHour, array $fields = ['count']): array
    {
        $allHours = range($startHour, $endHour);

        $dataByHour = collect($data)->keyBy(fn($row) => (int)($row['hour'] ?? 0))->toArray();

        $result = ['labels' => []];
        foreach ($fields as $field) {
            $result[$field] = [];
        }

        foreach ($allHours as $hour) {
            $label = str_pad($hour, 2, '0', STR_PAD_LEFT) . ':00';
            $result['labels'][] = $label;

            $row = $dataByHour[$hour] ?? null;

            foreach ($fields as $field) {
                $value = $row ? ($row[$field] ?? 0) : 0;
                $result[$field][] = $value;
            }
        }

        return $result;
    }
}
