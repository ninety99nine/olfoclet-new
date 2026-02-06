<?php
// Turn off error reporting to STDERR so it doesn't mess up Docker logs
// We will capture errors internally and return them as JSON
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Simple router for the built-in server
$uri = $_SERVER['REQUEST_URI'];
if ($uri !== '/run') {
    http_response_code(404);
    echo json_encode(['error' => 'Not Found']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

// 1. Read Input
$rawInput = file_get_contents('php://input');
$payload = json_decode($rawInput, true);

if (!$payload || !isset($payload['code'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON payload']);
    exit;
}

$code = $payload['code'];
$context = $payload['context'] ?? [];

// 2. Execute Code
try {
    // We wrap the eval in a closure to enforce local scope isolation
    $result = (function() use ($code, $context) {
        // Extract context array to variables
        extract($context);

        // Execute user code
        // The code MUST 'return' a value to be captured here
        return eval($code);
    })();

    // 3. Return Result
    header('Content-Type: application/json');
    echo json_encode(['result' => $result]);

} catch (ParseError $e) {
    http_response_code(400);
    echo json_encode(['error' => 'Parse Error: ' . $e->getMessage()]);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['error' => 'Runtime Error: ' . $e->getMessage()]);
}
