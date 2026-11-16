<?php
/**
 * MIDHILA VOYAGES — API: Get Accommodations by Destination
 * Returns JSON list of accommodations registered under a given destination
 */

header('Content-Type: application/json; charset=UTF-8');
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/database.php';
require_once dirname(__DIR__) . '/includes/functions.php';

$destinationId = isset($_GET['destination_id']) ? (int)$_GET['destination_id'] : 0;
$categorySlug = isset($_GET['category']) ? trim($_GET['category']) : '';
$lang = current_lang();

try {
    $db = getDB();
    $sql = "SELECT a.*, d.name as destination_name, d.slug as destination_slug 
            FROM accommodations a
            LEFT JOIN destinations d ON a.destination_id = d.id
            WHERE a.is_active = 1";
    $params = [];

    if ($destinationId > 0) {
        $sql .= " AND a.destination_id = ?";
        $params[] = $destinationId;
    }

    if (!empty($categorySlug)) {
        $sql .= " AND a.tourism_category_id IN (SELECT id FROM tourism_categories WHERE slug = ?)";
        $params[] = $categorySlug;
    }

    $sql .= " ORDER BY a.indicative_price ASC";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $results = [];
    foreach ($rows as $r) {
        $name = loc($r, 'name');
        $results[] = [
            'id' => (int)$r['id'],
            'destination_id' => (int)$r['destination_id'],
            'destination_name' => $r['destination_name'] ?? '',
            'name' => $name,
            'category' => $r['category'],
            'location_address' => $r['location_address'],
            'room_categories' => $r['room_categories'],
            'room_views' => $r['room_views'],
            'room_facilities' => $r['room_facilities'],
            'food_availability' => $r['food_availability'],
            'food_types' => $r['food_types'],
            'sightseeing_services' => $r['sightseeing_services'],
            'additional_facilities' => $r['additional_facilities'],
            'indicative_price' => (float)$r['indicative_price'],
            'formatted_price' => format_price((float)$r['indicative_price']),
            'featured_image' => $r['featured_image'],
            'special_instructions' => $r['special_instructions']
        ];
    }

    echo json_encode([
        'success' => true,
        'count' => count($results),
        'accommodations' => $results
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching accommodations: ' . $e->getMessage()
    ]);
}
