<?php
/**
 * MIDHILA VOYAGES — Payment Verification & Webhook Callback API
 * Verifies gateway cryptographic signatures and updates booking records idempotently
 */

header('Content-Type: application/json; charset=utf-8');

require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/database.php';
require_once dirname(__DIR__) . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method Not Allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

$bookingRef = trim($input['booking_reference'] ?? '');
$orderId = trim($input['razorpay_order_id'] ?? $input['order_id'] ?? '');
$paymentId = trim($input['razorpay_payment_id'] ?? $input['payment_id'] ?? '');
$signature = trim($input['razorpay_signature'] ?? $input['signature'] ?? '');
$isTestMock = !empty($input['is_test_mock']);

if (empty($bookingRef) || empty($orderId) || empty($paymentId)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing critical payment verification parameters.']);
    exit;
}

try {
    $db = getDB();

    // 1. Fetch existing payment record
    $stmt = $db->prepare("SELECT * FROM `payments` WHERE `gateway_order_id` = ? AND `booking_reference` = ? LIMIT 1");
    $stmt->execute([$orderId, $bookingRef]);
    $paymentRecord = $stmt->fetch();

    if (!$paymentRecord) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'No matching payment order found for this reference.']);
        exit;
    }

    // 2. Idempotency Check: if already captured, do not process twice
    if ($paymentRecord['status'] === 'captured') {
        echo json_encode([
            'success' => true,
            'message' => 'Payment already verified and captured.',
            'booking_reference' => $bookingRef,
            'redirect_url' => BASE_URL . '/payment-success.php?ref=' . urlencode($bookingRef)
        ]);
        exit;
    }

    // 3. Cryptographic Signature Verification
    $keySecret = get_setting('razorpay_key_secret', 'rzp_secret_demo_123456789');
    $paymentMode = get_setting('payment_mode', 'test');
    $signatureValid = false;

    if ($paymentMode === 'test' && $isTestMock) {
        // Test sandbox mock confirmation
        $signatureValid = true;
    } else {
        // Formal HMAC SHA256 verification according to Razorpay specification
        $expectedSignature = hash_hmac('sha256', $orderId . '|' . $paymentId, $keySecret);
        $signatureValid = hash_equals($expectedSignature, $signature);
    }

    if (!$signatureValid) {
        // Record failed payment attempt
        $failStmt = $db->prepare("UPDATE `payments` SET `status` = 'failed', `raw_response` = ? WHERE `id` = ?");
        $failStmt->execute([json_encode($input), $paymentRecord['id']]);

        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid payment signature. Verification failed.',
            'redirect_url' => BASE_URL . '/payment-failure.php?ref=' . urlencode($bookingRef)
        ]);
        exit;
    }

    // 4. Update Database Transaction
    $db->beginTransaction();

    // Update payment record to captured
    $updPayment = $db->prepare("
        UPDATE `payments` 
        SET `gateway_payment_id` = ?, `gateway_signature` = ?, `status` = 'captured',
            `raw_response` = ?, `verified_at` = NOW()
        WHERE `id` = ?
    ");
    $updPayment->execute([
        $paymentId, $signature, json_encode($input), $paymentRecord['id']
    ]);

    // Update booking status to confirmed & payment status
    $stmtBk = $db->prepare("SELECT `total_amount` FROM `bookings` WHERE `booking_reference` = ? LIMIT 1");
    $stmtBk->execute([$bookingRef]);
    $booking = $stmtBk->fetch();

    $newPayStatus = ($paymentRecord['amount'] >= ($booking['total_amount'] ?? 0)) ? 'paid' : 'partial';

    $updBk = $db->prepare("
        UPDATE `bookings`
        SET `booking_status` = 'confirmed', `payment_status` = ?, `updated_at` = NOW()
        WHERE `booking_reference` = ?
    ");
    $updBk->execute([$newPayStatus, $bookingRef]);

    $db->commit();

    log_activity(null, 'payment_verified', "Payment captured for booking ref {$bookingRef}. Amount: INR {$paymentRecord['amount']}");

    echo json_encode([
        'success' => true,
        'message' => 'Payment verified successfully.',
        'booking_reference' => $bookingRef,
        'redirect_url' => BASE_URL . '/payment-success.php?ref=' . urlencode($bookingRef)
    ]);

} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    error_log("Payment Verification Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Internal error during payment verification.']);
}
