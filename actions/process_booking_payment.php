<?php
/**
 * Process Booking Payment
 * Verifies payment and creates booking with payment record
 */

header('Content-Type: application/json');

require_once '../settings/core.php';
require_once '../settings/paystack_config.php';
require_once '../controllers/booking_controller.php';
require_once '../classes/payment_class.php';

// Check authentication
if (!is_logged_in()) {
    echo json_encode(['status' => 'error', 'message' => 'Authentication required']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$reference = isset($input['reference']) ? trim($input['reference']) : '';

if (empty($reference)) {
    echo json_encode(['status' => 'error', 'message' => 'Payment reference required']);
    exit();
}

try {
    // Get booking data from session
    if (!isset($_SESSION['pending_booking'])) {
        throw new Exception('Booking data not found. Please try booking again.');
    }
    
    $booking_data = $_SESSION['pending_booking'];
    
    // Verify payment reference matches
    if ($booking_data['payment_reference'] !== $reference) {
        throw new Exception('Payment reference mismatch');
    }
    
    // Verify payment with Paystack
    $verification = paystack_verify_transaction($reference);
    
    if (!isset($verification['status']) || $verification['status'] !== true) {
        throw new Exception($verification['message'] ?? 'Payment verification failed');
    }
    
    $payment_data = $verification['data'];
    
    // Check payment status
    if (!isset($payment_data['status']) || $payment_data['status'] !== 'success') {
        throw new Exception('Payment was not successful');
    }
    
    // Verify amount matches
    $expected_amount = ghs_to_pesewas($booking_data['estimated_price']);
    if ($payment_data['amount'] != $expected_amount) {
        throw new Exception('Payment amount mismatch');
    }
    
    // Verify customer matches
    $customer_id = get_user_id();
    if ($booking_data['customer_id'] != $customer_id) {
        throw new Exception('Customer mismatch');
    }
    
    // Create booking (service_id is NULL for now)
    $booking_result = create_booking_ctr(
        $booking_data['customer_id'],
        $booking_data['worker_id'],
        null, // service_id - nullable
        $booking_data['booking_date'],
        $booking_data['booking_time'],
        $booking_data['service_address'],
        $booking_data['estimated_price'],
        $booking_data['customer_notes']
    );
    
    if (!$booking_result || !isset($booking_result['booking_id'])) {
        throw new Exception('Failed to create booking');
    }
    
    $booking_id = $booking_result['booking_id'];
    $booking_reference = $booking_result['booking_reference'];
    
    // Create payment record
    $payment_class = new payment_class();
    
    // Calculate commission (7% from customer, 5% from worker = 12% total)
    $customer_commission = $booking_data['estimated_price'] * 0.07;
    $worker_commission = $booking_data['estimated_price'] * 0.05;
    $worker_payout = $booking_data['estimated_price'] - $worker_commission;
    
    $payment_id = $payment_class->record_payment(
        $booking_id,
        $booking_data['estimated_price'],
        'paystack',
        $reference,
        'successful',
        $customer_commission,
        $worker_commission,
        $worker_payout
    );
    
    if (!$payment_id) {
        // Booking created but payment record failed - log error
        error_log("Payment record failed for booking $booking_id, reference $reference");
    }
    
    // Clear session data
    unset($_SESSION['pending_booking']);
    unset($_SESSION['paystack_ref']);
    unset($_SESSION['paystack_amount']);
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Booking created successfully',
        'booking_id' => $booking_id,
        'booking_reference' => $booking_reference
    ]);
    
} catch (Exception $e) {
    error_log("Booking payment processing error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
