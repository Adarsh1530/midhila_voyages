<?php
/**
 * MIDHILA VOYAGES — Payment Order Creation API
 * Generates Booking & Payment order records securely on server
 */

header('Content-Type: application/json; charset=utf-8');

require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/database.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/csrf.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method Not Allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

// Verify CSRF
$csrfToken = $input['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!verify_csrf_token($csrfToken)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Security token expired. Please refresh the page.']);
    exit;
}

$packageId = (int)($input['package_id'] ?? 0);
$customerName = trim($input['customer_name'] ?? '');
$customerEmail = trim($input['customer_email'] ?? '');
$customerPhone = trim($input['customer_phone'] ?? '');
$travelDate = trim($input['travel_date'] ?? '');
$travellers = max(1, (int)($input['travellers_count'] ?? 1));
$paymentType = trim($input['payment_type'] ?? 'deposit'); // 'deposit' or 'full'
$specialRequests = trim($input['special_requests'] ?? '');

if (empty($customerName) || empty($customerEmail) || empty($customerPhone) || empty($travelDate)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Please fill in all required customer and travel details.']);
    exit;
}

try {
    $db = getDB();

    // Verify package existence and fetch price
    $stmt = $db->prepare("SELECT * FROM `packages` WHERE `id` = ? AND `is_published` = 1 LIMIT 1");
    $stmt->execute([$packageId]);
    $package = $stmt->fetch();

    if (!$package) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Selected package is currently unavailable.']);
        exit;
    }

    $packageBasePrice = (float)($package['starting_price'] ?? 15000);
    if ($packageBasePrice <= 0) {
        $packageBasePrice = 15000;
    }

    $totalCalculated = $packageBasePrice * $travellers;

    // Amount to charge now
    if ($paymentType === 'deposit') {
        // Flat deposit of ₹5,000 per person or 25% of total, whichever is reasonable
        $chargeAmount = min(5000 * $travellers, $totalCalculated);
    } else {
        $chargeAmount = $totalCalculated;
    }

    // Begin Database Transaction
    $db->beginTransaction();

    $bookingRef = generate_reference('MV-BKG');
    $currency = 'INR';

    // 1. Insert Booking record
    $insBooking = $db->prepare("
        INSERT INTO `bookings` (
            `booking_reference`, `package_id`, `customer_name`, `customer_email`,
            `customer_phone`, `travel_date`, `travellers_count`, `total_amount`,
            `currency`, `booking_status`, `payment_status`, `special_requests`
        ) VALUES (
            ?, ?, ?, ?,
            ?, ?, ?, ?,
            ?, 'pending', 'unpaid', ?
        )
    ");
    $insBooking->execute([
        $bookingRef, $packageId, $customerName, $customerEmail,
        $customerPhone, $travelDate, $travellers, $totalCalculated,
        $currency, $specialRequests
    ]);
    $bookingId = (int)$db->lastInsertId();

    // 2. Generate Razorpay / Sandbox order ID
    $gatewayOrderId = 'order_' . bin2hex(random_bytes(10));
    $gatewayKey = get_setting('razorpay_key_id', 'rzp_test_midhila_demo');
    $paymentMode = get_setting('payment_mode', 'test');

    // 3. Insert Payment audit record
    $insPayment = $db->prepare("
        INSERT INTO `payments` (
            `booking_id`, `booking_reference`, `payment_gateway`, `gateway_order_id`,
            `amount`, `currency`, `status`
        ) VALUES (
            ?, ?, 'razorpay', ?,
            ?, ?, 'pending'
        )
    ");
    $insPayment->execute([
        $bookingId, $bookingRef, $gatewayOrderId,
        $chargeAmount, $currency
    ]);

    $db->commit();

    // Send back parameters for frontend checkout
    echo json_encode([
        'success' => true,
        'booking_reference' => $bookingRef,
        'booking_id' => $bookingId,
        'order_id' => $gatewayOrderId,
        'amount' => $chargeAmount,
        'amount_cents' => (int)($chargeAmount * 100),
        'currency' => $currency,
        'key_id' => $gatewayKey,
        'payment_mode' => $paymentMode,
        'package_title' => $package['title'],
        'customer_name' => $customerName,
        'customer_email' => $customerEmail,
        'customer_phone' => $customerPhone
    ]);

} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    error_log("Payment Order Creation Failed: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Unable to create payment order. Please try again.']);
}
