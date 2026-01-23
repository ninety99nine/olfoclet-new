<?php

namespace Database\Seeders;

use App\Models\UssdAccount;
use App\Models\UssdSession;
use App\Models\UssdSessionStep;
use App\Models\UssdSessionFlag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UssdTestDataSeeder extends Seeder
{
    /**
     * Run the seeder.
     */
    public function run(): void
    {
        $testAppId = DB::table('apps')->latest('created_at')->value('id');

        // === CLEAR EXISTING DATA ===
        $this->clearTables();

        // === 1. Create 50 USSD Accounts ===
        $accounts = $this->createTestAccounts($testAppId);
        UssdAccount::insert($accounts);

        // === 2. Create 50 USSD Sessions + Steps + Flags ===
        $sessions = [];
        $steps = [];
        $flags = [];

        foreach (range(1, 50) as $i) {
            $account = $accounts[array_rand($accounts)];

            $successful = rand(0, 1) === 1; // ~50% success
            $simulated = rand(0, 3) === 0;  // ~25% simulator
            $totalSteps = $successful ? rand(1, 5) : rand(1, 8);

            // Session duration will be calculated from steps
            $sessionId = (string) Str::uuid();

            // Base timestamp = created_at (random in last 90 days)
            $createdAt = now()->subDays(rand(0, 90))->subHours(rand(0, 23))->subMinutes(rand(0, 59));
            // updated_at placeholder — will be adjusted later based on steps
            $updatedAt = $createdAt->copy()->addSeconds(10); // temporary

            $sessions[] = [
                'id'                      => $sessionId,
                'msisdn'                  => $account['msisdn'],
                'shortcode'               => '*123' . rand(1, 9),
                'country'                 => $account['country'],
                'network'                 => $account['network'],
                'session_id'              => Str::random(32),
                'successful'              => $successful,
                'error_message'           => $successful ? null : 'Timeout after ' . rand(3, 12) . ' steps',
                'total_steps'             => $totalSteps,
                'open_flags_count'        => 0, // updated later
                'simulated'               => $simulated,
                'last_step_content'       => $successful ? 'Thank you for using our service' : 'Session timed out',
                'total_duration_ms' => 0, // ← will be updated after steps
                'app_id'                  => $testAppId,
                'ussd_account_id'         => $account['id'],
                'created_at'              => $createdAt,
                'updated_at'              => $updatedAt,
            ];

            // === 3. Create steps (spread within session duration) ===
            $sessionTotalDurationMs = 0;

            for ($s = 1; $s <= $totalSteps; $s++) {
                // Each step duration: 1–10 seconds (1000–10000 ms)
                $stepDurationMs = rand(1000, 10000);
                $sessionTotalDurationMs += $stepDurationMs;

                // Progressive timestamps — each step starts after previous one
                $stepTimeOffset = rand(1, max(3, $stepDurationMs / 1000 + rand(2, 12)));
                $stepCreatedAt = $createdAt->copy()->addSeconds($stepTimeOffset * ($s - 1) + rand(0, 8));
                $stepUpdatedAt = $stepCreatedAt->copy()->addSeconds(rand(1, 12));

                // ────────────────────────────────────────────────
                //  Decide if this step has a user reply
                // ────────────────────────────────────────────────
                $hasReply = true;

                // Last step is more likely to have NO reply (especially if failed)
                if ($s === $totalSteps) {
                    if (!$successful) {
                        // Failed session → 70–90% chance last step has no reply
                        $hasReply = rand(0, 9) >= 7 ? false : true;
                    } else {
                        // Successful session → 30–50% chance last step has no reply (final message)
                        $hasReply = rand(0, 9) >= 5 ? false : true;
                    }
                }
                // Early steps almost always have reply (unless very short failed session)
                elseif ($totalSteps <= 2 && !$successful && rand(0, 4) === 0) {
                    $hasReply = false;
                }

                $replyValue = $hasReply ? ['1', '2', '58723', '2389', '1276', 'yes', 'no'][rand(0, 6)] : null;

                $steps[] = [
                    'id'                        => (string) Str::uuid(),
                    'ussd_session_id'           => $sessionId,
                    'step_id'                   => 'step_' . $s,
                    'step_title'                => 'Step ' . $s,
                    'step_content'              => 'Content of step ' . $s . ($successful ? ' - OK' : ' - Error'),
                    'reply'                     => $replyValue,
                    'paginated'                 => rand(0, 4) === 0,
                    'page_number'               => rand(0, 4) === 0 ? rand(1, 4) : 0,
                    'total_pages'               => rand(0, 4) === 0 ? rand(2, 6) : 0,
                    'terminated_by_system'      => !$successful && $s === $totalSteps && !$hasReply,
                    'total_failed_validation'   => !$successful && $hasReply ? rand(0, 3) : 0,
                    'total_duration_ms'         => $stepDurationMs,
                    'successful'                => $successful || rand(0, 3) === 0,
                    'error_message'             => $successful || $hasReply ? null : 'Invalid input at step ' . $s,
                    'created_at'                => $stepCreatedAt,
                    'updated_at'                => $stepUpdatedAt,
                ];
            }

            // Update session with real total duration from steps
            $sessions[count($sessions) - 1]['total_duration_ms'] = $sessionTotalDurationMs;

            // Update updated_at to reflect total session time
            $sessions[count($sessions) - 1]['updated_at'] = $createdAt->copy()->addMilliseconds($sessionTotalDurationMs);

            // === 4. Create flags (more likely on failed sessions) ===
            if (rand(0, 1) === 1 || !$successful) {
                $flagCount = rand(1, 3);
                for ($f = 1; $f <= $flagCount; $f++) {
                    $flags[] = [
                        'id'                => (string) Str::uuid(),
                        'ussd_session_id'   => $sessionId,
                        'category'          => ['bug', 'ux', 'performance', 'content', 'fraud'][rand(0, 4)],
                        'priority'          => ['low', 'medium', 'high'][rand(0, 2)],
                        'comment'           => 'Test flag #' . $f . ' on session ' . substr($sessionId, 0, 8),
                        'status'            => rand(0, 2) === 0 ? 'resolved' : 'open',
                        'resolved_at'       => rand(0, 2) === 0 ? now()->subDays(rand(1, 30)) : null,
                        'resolution_comment'=> rand(0, 2) === 0 ? 'Fixed in next release' : null,
                        'created_by'        => null,
                        'resolved_by'       => null,
                        'created_at'        => now()->subDays(rand(0, 60)),
                        'updated_at'        => now()->subDays(rand(0, 30)),
                    ];
                }
            }
        }

        // Bulk inserts
        UssdSession::insert($sessions);
        UssdSessionStep::insert($steps);
        UssdSessionFlag::insert($flags);

        // Update open flags count
        $this->updateOpenFlagsCount($sessions);

        // Optional: Update account aggregates (total_duration_ms, etc.)
        $this->updateAccountAggregates($accounts);

        $this->command->info("Seeded: 50 accounts, " . count($sessions) . " sessions, " . count($steps) . " steps, " . count($flags) . " flags.");
    }

    private function clearTables(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        DB::table('ussd_session_flags')->truncate();
        DB::table('ussd_session_steps')->truncate();
        DB::table('ussd_sessions')->truncate();
        DB::table('ussd_accounts')->truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('Cleared previous test data');
    }

    private function createTestAccounts(string $testAppId): array
    {
        $accounts = [];

        for ($i = 1; $i <= 50; $i++) {
            $msisdn = '+2677' . str_pad($i, 7, '0', STR_PAD_LEFT);
            $country = rand(0, 8) === 0 ? 'ZA' : 'BW';
            $network = $country === 'ZA' ? 'MTN' : ['Orange', 'Mascom', 'BTC'][rand(0, 2)];

            $accounts[] = [
                'id'                        => (string) Str::uuid(),
                'msisdn'                    => $msisdn,
                'country'                   => $country,
                'network'                   => $network,
                'simulated'                 => rand(0, 1),
                'total_sessions'            => 0, // will be updated later
                'total_successful_sessions' => 0,
                'total_failed_sessions'     => 0,
                'total_mobile_sessions'     => 0,
                'total_simulator_sessions'  => 0,
                'total_steps'               => 0,
                'total_duration_ms' => 0, // ← will be updated
                'open_flags_count'          => 0,
                'blocked_at'                => rand(0, 10) === 0 ? now()->subDays(rand(1, 90)) : null,
                'app_id'                    => $testAppId,
                'created_at'                => now()->subDays(rand(1, 180)),
                'updated_at'                => now()->subDays(rand(0, 30)),
            ];
        }

        return $accounts;
    }

    private function updateOpenFlagsCount(array $sessions): void
    {
        foreach ($sessions as $session) {
            $openCount = DB::table('ussd_session_flags')
                ->where('ussd_session_id', $session['id'])
                ->where('status', 'open')
                ->count();

            DB::table('ussd_sessions')
                ->where('id', $session['id'])
                ->update(['open_flags_count' => $openCount]);
        }
    }

    /**
     * Update account aggregates based on seeded sessions and steps
     */
    private function updateAccountAggregates(array $accounts): void
    {
        foreach ($accounts as $account) {
            $accountId = $account['id'];

            $sessionStats = DB::table('ussd_sessions')
                ->where('ussd_account_id', $accountId)
                ->selectRaw('
                    COUNT(*) as total_sessions,
                    COALESCE(SUM(CASE WHEN successful = 1 THEN 1 ELSE 0 END), 0) as total_successful_sessions,
                    COALESCE(SUM(CASE WHEN successful = 0 THEN 1 ELSE 0 END), 0) as total_failed_sessions,
                    COALESCE(SUM(CASE WHEN simulated = 0 THEN 1 ELSE 0 END), 0) as total_mobile_sessions,
                    COALESCE(SUM(CASE WHEN simulated = 1 THEN 1 ELSE 0 END), 0) as total_simulator_sessions,
                    COALESCE(SUM(total_steps), 0) as total_steps,
                    COALESCE(SUM(total_duration_ms), 0) as total_duration_ms,
                    COALESCE(SUM(open_flags_count), 0) as open_flags_count
                ')
                ->first();

            DB::table('ussd_accounts')
                ->where('id', $accountId)
                ->update([
                    'total_sessions'              => $sessionStats->total_sessions ?? 0,
                    'total_successful_sessions'   => $sessionStats->total_successful_sessions,
                    'total_failed_sessions'       => $sessionStats->total_failed_sessions,
                    'total_mobile_sessions'       => $sessionStats->total_mobile_sessions,
                    'total_simulator_sessions'    => $sessionStats->total_simulator_sessions,
                    'total_steps'                 => $sessionStats->total_steps,
                    'total_duration_ms' => $sessionStats->total_duration_ms,
                    'open_flags_count'            => $sessionStats->open_flags_count,
                    'updated_at'                  => now(),
                ]);
        }
    }
}
