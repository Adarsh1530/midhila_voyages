<?php
/**
 * MIDHILA VOYAGES — Mapped Places API Endpoint
 * 
 * Returns verified OpenStreetMap & admin-approved places for a destination
 * formatted for Leaflet interactive maps and enquiry selection.
 */

header('Content-Type: application/json; charset=UTF-8');
header('X-Content-Type-Options: nosniff');

require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/database.php';
require_once dirname(__DIR__) . '/includes/overpass_service.php';

$destinationId = isset($_GET['destination_id']) ? (int)$_GET['destination_id'] : 0;
$category = isset($_GET['category']) ? trim($_GET['category']) : 'all';
$forceRefresh = isset($_GET['refresh']) && $_GET['refresh'] === '1';

// Restrict force refresh to authenticated admins
if ($forceRefresh) {
    session_start();
    if (empty($_SESSION['admin_logged_in'])) {
        $forceRefresh = false;
    }
}

if ($destinationId <= 0) {
    echo json_encode([
        'success' => false,
        'error'   => 'Valid destination_id is required.',
        'places'  => [],
    ]);
    exit;
}

$validCategories = ['all', 'hotel', 'resort', 'restaurant', 'cafe', 'attraction'];
if (!in_array($category, $validCategories)) {
    $category = 'all';
}

try {
    $result = OverpassService::getPlacesForDestination($destinationId, $category, $forceRefresh);
    echo json_encode($result);
} catch (Exception $e) {
    error_log("Places API error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error'   => 'Failed to retrieve destination places.',
        'places'  => [],
    ]);
}
