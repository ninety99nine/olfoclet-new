<?php

namespace App\Services;

use Exception;
use Throwable;
use App\Models\App;
use App\Models\Version;
use App\Models\Deployment;
use App\Models\UssdAccount;
use App\Models\UssdSession;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class UssdService
{
    /**
     * Simulate USSD.
     *
     * @param array $data
     * @return array
     */
    public function simulateUssd(array $data): array
    {
        // We use 'where' then 'firstOrFail' to help static analysis tools understand the return type
        $version = Version::where('id', $data['version_id'])->with('app')->firstOrFail();

        $data['session_id'] = $data['session_id'] ?? Str::uuid()->toString();

        $standardResponse = $this->processSession(
            $version->app,
            $version,
            $data
        );

        return $standardResponse;
    }

    /**
     * Launch USSD.
     *
     * @param Request $request
     * @param Deployment $deployment
     * @return mixed
     */
    public function launchUssd(Request $request, Deployment $deployment): mixed
    {
        $deployment->load(['app', 'version']);

        try {

            // 1. Parse & Transform Incoming Request
            $incomingPayload = $this->parseIncomingRequest($request, $deployment->incoming_format);

            $standardPayload = $this->executeCode(
                $deployment->transform_request_language,
                $deployment->transform_request_code,
                ['incoming' => $incomingPayload]
            );

            // 2. Validate Standard Payload
            $this->validateStandardPayload($standardPayload);

            // 3. Process Session
            if ($deployment->active) {

                $outgoingPayload = $this->processSession(
                    $deployment->app,
                    $deployment->version,
                    $standardPayload,
                    $deployment
                );

            } else {

                $outgoingPayload = [
                    'type' => 'stop',
                    'message' => 'Service unavailable.',
                    'session_id' => $standardPayload['session_id']
                ];

            }

            // 4. Transform Response (Standard -> MNO format)
            $mnoResponseContent = $this->executeCode(
                $deployment->transform_response_language,
                $deployment->transform_response_code,
                ['outgoing' => $outgoingPayload]
            );

            // 5. Handle JSON specifically (Early Return)
            if ($deployment->outgoing_format === 'json') {
                return response()->json($mnoResponseContent);
            }

            // 6. Handle Other Formats (XML/Text)
            $contentType = match ($deployment->outgoing_format) {
                'xml'  => 'application/xml',
                'text' => 'text/plain',
                default => 'text/plain'
            };

            $finalContent = is_array($mnoResponseContent)
                ? json_encode($mnoResponseContent)
                : $mnoResponseContent;

            return response($finalContent, 200)->header('Content-Type', $contentType);

        } catch (Throwable $e) {

            // Return a safe error response formatted for the MNO if possible,
            // otherwise a generic 500.
            return response("Error processing USSD: " . $e->getMessage(), 500);

        }
    }

    /**
     * Process USSD session using the Version Builder with State Replay.
     *
     * @param App $app
     * @param Version $version
     * @param array $payload
     * @param Deployment|null $deployment
     * @return array
     */
    private function processSession(App $app, Version $version, array $payload, ?Deployment $deployment = null): array
    {
        $msisdn = $payload['phone_number'];
        $isSimulated = is_null($deployment);

        // Explicitly cast to array to resolve Intelephense P1006 error
        $builder = (array) $version->builder;

        // 1. Find or Create the UssdAccount
        $account = UssdAccount::firstOrCreate(
            [
                'msisdn'    => $msisdn,
                'app_id'    => $app->id,
                'simulated' => $isSimulated
            ],
            [
                'total_sessions' => 0,
                'total_steps'    => 0,
                'open_flags_count' => 0,
                'country'   => $deployment ? $deployment->country : null,
                'network'   => $deployment ? $deployment->network : null,
            ]
        );

        if ($account->blocked_at) {
            return [
                'message' => 'This account access has been suspended.',
                'session_id' => $payload['session_id'],
                'type' => 'stop'
            ];
        }

        // 2. Find or Create the UssdSession
        $session = UssdSession::firstOrCreate(
            ['session_id' => $payload['session_id']],
            [
                'msisdn'            => $msisdn,
                'app_id'            => $app->id,
                'ussd_account_id'   => $account->id,
                'shortcode'         => $payload['service_code'] ?? null,
                'country'           => $account->country,
                'network'           => $account->network,
                'simulated'         => $isSimulated,
                'successful'        => true,
                'last_step_content' => null,
                'total_steps'       => 0,
                'open_flags_count'  => 0,
                'total_duration_ms' => 0,
                'history'           => []
            ]
        );

        // Retrieve existing history
        $history = $session->history ?? [];

        // Append new input to history immediately if this is a continuation
        // We will validate this inside rebuildState
        if (!$session->wasRecentlyCreated) {
            $history[] = $payload['text'] ?? '';
        }

        // 3. Rebuild State (Single Pass)
        // Traverses the entire history including the new input.
        // If the new input is invalid, $validationError will be populated,
        // and $cleanHistory will NOT include the invalid input.
        [$currentStepId, $variables, $cleanHistory, $validationError] = $this->rebuildState($builder, $history);

        if($validationError) {
            $message = $validationError;
        }else{

            // 4. Render the Current Step Content
            $currentStep = $currentStepId ? ($builder['steps'][$currentStepId] ?? null) : null;

            if (!$currentStep) {
                return [
                    'type' => 'stop',
                    'message' => 'Step does not exist.',
                    'session_id' => $payload['session_id']
                ];
            }

            $message = $this->renderScreenContent($currentStep, $builder, $variables);
        }

        // Check if the step is terminal
        $responseType = ($currentStep['is_terminal'] ?? false) ? 'stop' : 'continue';

        // 6. Global Stat & Persistence Updates
        if ($session->wasRecentlyCreated) {
            $account->increment('total_sessions');
            if ($isSimulated) {
                $account->increment('total_simulator_sessions');
            } else {
                $account->increment('total_mobile_sessions');
            }
        }

        $session->increment('total_steps');
        $account->increment('total_steps');

        // Update the session
        $session->update([
            'last_step_content' => $message,
            'history' => $cleanHistory
        ]);

        $account->touch();

        $account->update([
            'total_duration_ms' => $account->sessions()->sum('total_duration_ms')
        ]);

        return [
            'message'    => $message,
            'type'       => $responseType,
            'session_id' => $payload['session_id'],
            'current_step_id' => $currentStepId // Added for simulator highlighting
        ];
    }

    /**
     * Replays the session history to rebuild the current step ID and variables.
     * Also handles validation of every input in the history stack.
     *
     * @param array $builder
     * @param array $history
     * @return array [string|null $currentStepId, array $variables, array $validHistory, string|null $validationError]
     */
    private function rebuildState(array $builder, array $history): array
    {
        $variables = [];
        $historyIndex = 0;
        $validHistory = [];
        $validationError = null;
        $currentStepId = $builder['initial_step_id'];

        // Loop until we run out of valid steps
        while ($currentStepId) {

            $step = $builder['steps'][$currentStepId] ?? null;

            if (!$step) {
                // Step ID points to nothing
                return [null, $variables, $validHistory, null];
            }

            // Case 1: Decision Point
            if ($step['type'] === 'decision_point') {
                $nextStepId = $this->evaluateDecision($step, $variables);

                if (!$nextStepId) {
                    // Decision resulted in NULL (No match & no default). Stop.
                    return [null, $variables, $validHistory, null];
                }

                $currentStepId = $nextStepId;
                continue; // Loop again immediately with the new Step ID
            }

            // Case 2: Interactive Screen
            if ($step['type'] === 'interactive_screen') {

                // Check if we have an input available to "answer" this screen
                if (isset($history[$historyIndex])) {

                    $inputToProcess = $history[$historyIndex];
                    $inputFeature = $this->findFeatureByType($step, $builder, 'input');

                    if ($inputFeature) {

                        // Validate the input
                        $error = $this->validateUserInput($inputToProcess, $inputFeature, $builder);

                        if ($error) {
                            // Validation Failed:
                            // We stop processing here. The current step remains THIS screen.
                            // We return the error so it can be displayed.
                            // We DO NOT add this input to $validHistory.
                            $validationError = $error;
                            break;
                        }

                        // Validation Passed: Record and Advance
                        $validHistory[] = $inputToProcess;
                        $historyIndex++;

                        if (!empty($inputFeature['variable'])) {
                            $variables[$inputFeature['variable']] = $inputToProcess;
                        }

                        $currentStepId = $inputFeature['next_step_id'];
                        continue; // Loop again

                    }

                    // Input exists in history, but screen has no input feature?
                    // This is invalid/stale history. Discard and Stop.
                    break;
                }

                // No history left. This is the "Live" screen.
                break;
            }
        }

        return [$currentStepId, $variables, $validHistory, $validationError];
    }

    /**
     * Evaluate rules for a decision point and return the destination step ID.
     */
    private function evaluateDecision(array $step, array $variables): ?string
    {
        foreach ($step['rules'] as $rule) {
            $match = true;
            foreach ($rule['checks'] as $check) {

                $varValue = $variables[$check['variable']] ?? null;
                $targetValue = $this->resolveVariables($check['value'], $variables);

                if (!$this->compareValues($varValue, $check['operator'], $targetValue)) {
                    $match = false;
                    break;
                }
            }

            if ($match) {
                return $rule['destination'];
            }
        }

        return $step['default_destination'];
    }

    /**
     * Render screen content from features.
     */
    private function renderScreenContent(array $step, array $builder, array $variables): string
    {
        $contentParts = [];
        $features = $builder['features'] ?? [];

        foreach ($step['feature_ids'] as $id) {
            $feature = $features[$id] ?? null;
            if (!$feature) continue;

            if ($feature['type'] === 'basic content') {
                $contentParts[] = $this->resolveVariables($feature['content'], $variables);
            }

            if ($feature['type'] === 'code content') {
                $code = $this->resolveVariables($feature['content'], $variables);
                $contentParts[] = $this->executeCode($feature['language'], $code, $variables);
            }
        }

        return implode("\n", $contentParts);
    }

    /**
     * Helper to replace variable placeholders in text.
     */
    private function resolveVariables(mixed $text, array $variables): mixed
    {
        if (is_string($text) && str_contains($text, '@')) {
            foreach ($variables as $key => $val) {
                $text = str_replace("@{$key}", $val, $text);
            }
        }
        return $text;
    }

    /**
     * Validate user input against step rules.
     */
    private function validateUserInput(string $input, array $feature, array $builder): ?string
    {
        foreach ($feature['validation_rule_ids'] as $ruleId) {
            $rule = $builder['validation_rules'][$ruleId] ?? null;
            if (!$rule) continue;

            $val = $rule['value'] ?? null;

            $isValid = match ($rule['type']) {
                'alpha'                 => ctype_alpha(str_replace(' ', '', $input)),
                'numeric'               => is_numeric($input),
                'alphanumeric'          => ctype_alnum(str_replace(' ', '', $input)),
                'min_length'            => strlen($input) >= (int)$val,
                'max_length'            => strlen($input) <= (int)$val,
                'equal_length'          => strlen($input) === (int)$val,
                'eq'                    => $input == $val,
                'neq'                   => $input != $val,
                'lt'                    => is_numeric($input) && $input < $val,
                'lte'                   => is_numeric($input) && $input <= $val,
                'gt'                    => is_numeric($input) && $input > $val,
                'gte'                   => is_numeric($input) && $input >= $val,
                'between_inclusive'     => false,
                'between_exclusive'     => false,
                'email'                 => filter_var($input, FILTER_VALIDATE_EMAIL),
                'money'                 => preg_match('/^\d+(\.\d{1,2})?$/', $input),
                'date_ddmmyyyy'         => $this->validateDate($input, 'dmY'),
                'date_dd_mm_yyyy'       => $this->validateDate($input, 'd/m/Y'),
                'date_dd_mm_yyyy_dash'  => $this->validateDate($input, 'd-m-Y'),
                'no_spaces'             => !str_contains($input, ' '),
                'regex'                 => preg_match($rule['pattern'], $input),
                default                 => true
            };

            if (!$isValid) {
                return $rule['message'];
            }
        }

        return null;
    }

    /**
     * Helper to validate dates
     */
    private function validateDate($date, $format = 'Y-m-d'): bool
    {
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

    /**
     * Helper to find specific feature in a step.
     */
    private function findFeatureByType(array $step, array $builder, string $type): ?array
    {
        foreach ($step['feature_ids'] as $id) {
            $feature = $builder['features'][$id] ?? null;
            if ($feature && $feature['type'] === $type) {
                return $feature;
            }
        }
        return null;
    }

    /**
     * Basic operator comparison.
     */
    private function compareValues($a, $operator, $b): bool
    {
        return match ($operator) {
            'contains'      => str_contains((string)$a, (string)$b),
            'not_contains'  => !str_contains((string)$a, (string)$b),
            'starts_with'   => str_starts_with((string)$a, (string)$b),
            'ends_with'     => str_ends_with((string)$a, (string)$b),
            'is_empty'      => empty($a),
            '=='            => $a == $b,
            '!='            => $a != $b,
            '>'             => $a > $b,
            '<'             => $a < $b,
            '>='            => $a >= $b,
            '<='            => $a <= $b,
            'in_list'       => in_array($a, array_map('trim', explode(',', (string)$b))),
            'not_in_list'   => !in_array($a, array_map('trim', explode(',', (string)$b))),
            'regex'         => (bool)preg_match((string)$b, (string)$a),
            'is_true'       => filter_var($a, FILTER_VALIDATE_BOOLEAN),
            'is_false'      => !filter_var($a, FILTER_VALIDATE_BOOLEAN),
            default         => false
        };
    }

    /**
     * Parse incoming request.
     *
     * @param Request $request
     * @param string $format
     * @return array
     */
    private function parseIncomingRequest(Request $request, string $format): array
    {
        if ($format === 'json') {
            return $request->all();
        }

        if ($format === 'xml') {
            $content = $request->getContent();
            $xml = simplexml_load_string($content);
            return json_decode(json_encode($xml), true);
        }

        return $request->all();
    }

    /**
     * Validate standard payload.
     *
     * @param array $payload
     * @return void
     */
    private function validateStandardPayload(array $payload): void
    {
        $required = ['session_id', 'phone_number'];

        foreach ($required as $field) {
            if (!array_key_exists($field, $payload)) {
                throw new Exception("Transformation failed. Missing required field: {$field}");
            }
        }
    }

    /**
     * Execute code in PHP, Python, or JavaScript via Sandbox.
     *
     * @param string $language
     * @param string $code
     * @param array $context
     * @return mixed
     * @throws Exception
     */
    private function executeCode(string $language, string $code, array $context): mixed
    {
        $sandboxUrl = match ($language) {
            'php' => config('services.sandbox.php'),
            'python' => config('services.sandbox.python'),
            'javascript', 'js' => config('services.sandbox.js'),
            default => null
        };

        if (!$sandboxUrl) {
            throw new Exception("Sandbox URL not configured for language: {$language}");
        }

        $safeContext = (object) $context;

        try {

            $response = Http::timeout(2)->post($sandboxUrl, [
                'code' => $code,
                'context' => $safeContext
            ]);

            if ($response->failed()) {
                $errorMsg = $response->json('error') ?? $response->body();
                throw new Exception("Sandbox Error: " . $errorMsg);
            }

            return $response->json('result');

        } catch (Exception $e) {
            throw new Exception("Sandbox Failed: " . $e->getMessage());
        }
    }
}
