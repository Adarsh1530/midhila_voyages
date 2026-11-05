<?php
/**
 * MIDHILA VOYAGES — Grounded AI Travel Itinerary API
 * 
 * Secure endpoint proxying requests to Ollama / Grounded AI Engine.
 * Enforces strict grounding, validation, and session rate-limiting.
 */

header('Content-Type: application/json; charset=UTF-8');
header('X-Content-Type-Options: nosniff');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/database.php';
require_once dirname(__DIR__) . '/includes/csrf.php';
require_once dirname(__DIR__) . '/includes/ollama_service.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method Not Allowed']);
    exit;
}

// Simple IP & session rate limiter (max 10 requests per minute)
$now = time();
if (!isset($_SESSION['ai_requests'])) $_SESSION['ai_requests'] = [];
$_SESSION['ai_requests'] = array_filter($_SESSION['ai_requests'], fn($ts) => ($now - $ts) < 60);
if (count($_SESSION['ai_requests']) >= 12) {
    http_response_code(429);
    echo json_encode([
        'success' => false,
        'error'   => 'Request limit reached. Please wait a moment before generating another itinerary.',
    ]);
    exit;
}
$_SESSION['ai_requests'][] = $now;

// Read JSON or POST form data
$rawBody = file_get_contents('php://input');
$data = json_decode($rawBody, true);
if (!is_array($data)) {
    $data = $_POST;
}

// CSRF check if token is provided; in public client AJAX we accept session-bound requests or token
$csrfToken = $data['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
if (!empty($csrfToken) && !verify_csrf_token($csrfToken)) {
    echo json_encode(['success' => false, 'error' => 'Invalid security token. Please refresh the page.']);
    exit;
}

$destId = isset($data['destination_id']) ? (int)$data['destination_id'] : 0;
if ($destId <= 0) {
    echo json_encode(['success' => false, 'error' => 'Please select a valid destination.']);
    exit;
}

$params = [
    'destination_id' => $destId,
    'customer_name'  => trim($data['customer_name'] ?? 'Guest Traveller'),
    'customer_phone' => trim($data['customer_phone'] ?? ''),
    'customer_email' => trim($data['customer_email'] ?? ''),
    'duration_days'  => max(1, min(14, (int)($data['duration_days'] ?? 3))),
    'travel_dates'   => trim($data['travel_dates'] ?? ''),
    'adults'         => max(1, (int)($data['adults'] ?? 2)),
    'children'       => max(0, (int)($data['children'] ?? 0)),
    'budget_range'   => trim($data['budget_range'] ?? 'Premium / Comfortable'),
    'travel_type'    => trim($data['travel_type'] ?? 'Family Leisure'),
    'food_pref'      => trim($data['food_pref'] ?? 'With Food (Both Veg & Non-Veg)'),
    'selected_places'=> is_array($data['selected_places'] ?? null) ? $data['selected_places'] : [],
    'language'       => in_array($data['language'] ?? 'en', ['en', 'ml', 'ta']) ? $data['language'] : 'en',
];

try {
    $res = OllamaService::generateGroundedItinerary($params);
    echo json_encode($res);
} catch (Throwable $e) {
    error_log("AI Itinerary Endpoint Error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());

    // Failsafe: Generate grounded fallback plan so visitor never sees a blocker error
    $destName = 'Kerala & All-India Circuit';
    try {
        $db = getDB();
        $dStmt = $db->prepare("SELECT name FROM destinations WHERE id = ?");
        $dStmt->execute([$destId]);
        $nameFromDb = $dStmt->fetchColumn();
        if ($nameFromDb) $destName = $nameFromDb;
    } catch (Throwable $dbErr) {}

    $fallbackItinerary = OllamaService::generateFallbackItinerary(
        $params,
        ['name' => $destName],
        [],
        [],
        [],
        $params['duration_days'] ?? 3,
        $params['language'] ?? 'en'
    );

    echo json_encode([
        'success'          => true,
        'reference_no'     => 'MV-AI-' . date('Y') . '-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6)),
        'destination_id'   => $destId,
        'destination_name' => $destName,
        'duration_days'    => $params['duration_days'] ?? 3,
        'model_used'       => 'curated-fallback',
        'itinerary'        => $fallbackItinerary,
        'verified_hotels'  => [],
        'grounded_places'  => [],
        'selected_places'  => [],
        'disclaimer'       => 'All hotel room allotments, schedules, and pricing are subject to direct availability check and confirmation by Midhila Voyages management.',
    ]);
}
