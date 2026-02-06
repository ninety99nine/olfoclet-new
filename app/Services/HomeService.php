<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\UssdSession;
use App\Models\UssdSessionFlag;

class HomeService extends BaseService
{
    /**
     * Get aggregated summary statistics for flags
     *
     * @return array
     */
    public function showHomeSummary(): array
    {
        $now = Carbon::now();

        $sessionsStats = UssdSession::query()
            ->selectRaw('
                COUNT(*) as total_sessions,
                SUM(CASE WHEN successful = 1 THEN 1 ELSE 0 END) as successful_sessions,
                SUM(total_duration_ms) as total_duration_ms,
                AVG(total_duration_ms) as avg_duration_ms,
                AVG(total_steps) as avg_steps
            ')
            ->first();

        $totalSessions     = (int) ($sessionsStats->total_sessions ?? 0);
        $successfulSessions = (int) ($sessionsStats->successful_sessions ?? 0);
        $successRate       = $totalSessions > 0 ? round(($successfulSessions / $totalSessions) * 100, 1) : 0;
        $avgDurationMs     = (int) round($sessionsStats->avg_duration_ms ?? 0);
        $avgSteps          = round($sessionsStats->avg_steps ?? 0, 1);

        // Recent sessions (this week)
        $recentStart = $now->clone()->startOfWeek();

        $recentStats = UssdSession::query()
            ->where('created_at', '>=', $recentStart)
            ->selectRaw('
                COUNT(*) as recent_sessions,
                SUM(CASE WHEN successful = 1 THEN 1 ELSE 0 END) as recent_successful
            ')->first();

        $recentSessions = (int) ($recentStats->recent_sessions ?? 0);

        $sessionsTrendDisplay = $recentSessions > 0
            ? "+{$recentSessions} this week"
            : "No sessions this week";

        $flagsStats = UssdSessionFlag::query()
            ->selectRaw('
                COUNT(CASE WHEN status = "open" THEN 1 END) as total_open
            ')
            ->first();

        $mostUrgentFlag = UssdSessionFlag::query()
            ->where('status', 'open')
            ->orderByRaw("
                CASE priority
                    WHEN 'critical' THEN 1
                    WHEN 'high'     THEN 2
                    WHEN 'medium'   THEN 3
                    WHEN 'low'      THEN 4
                    ELSE 5
                END ASC
            ")
            ->orderByDesc('created_at')
            ->select('id', 'ussd_session_id', 'priority', 'category', 'comment', 'created_at')
            ->first();

        return [
            'total_sessions'          => $totalSessions,
            'success_rate'            => $successRate,
            'avg_duration_ms'         => $avgDurationMs,
            'avg_steps'               => $avgSteps,
            'recent_sessions_trend'   => $sessionsTrendDisplay,
            'total_open_flags'        => (int) ($flagsStats->total_open ?? 0),
            'most_urgent_open_flag'   => $mostUrgentFlag
        ];
    }
}
