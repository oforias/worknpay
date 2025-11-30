<?php
/**
 * Booking Payment Initialization
 * Initialize Paystack payment for service booking
 */

header('Content-Type: application/json');

require_once '../settings/core.php';
require_once '../settings/paystack_config.php';

if (!is_logged_in()) {
    echo json_encode(['status' => 'error', 'message' => 'Please login']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$amount = isset($input['amount']) ? floatval($input['amount']) : 0;
$email = isset($input['email']) ? trim($input['email']) : '';
$booking_data = isset($input['booking_data']) ? $input['booking_data'] : null;

// Validate required fields
if ($amount <= 0 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid amount or email']);
    exit();
}

if (!$booking_data || !isset($booking_data['worker_id'], $booking_data['booking_date'], $booking_data['booking_time'], $booking_data['service_address'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing booking information']);
    exit();
}

try {
    $customer_id = get_user_id();
    $reference = 'BK-' . $customer_id . '-' . time();
    
    // Store complete booking data in session for callback
    $_SESSION['pending_booking'] = [
        'worker_id' => (int)$booking_data['worker_id'],
        'customer_id' => $customer_id,
        'booking_date' => $booking_data['booking_date'],
        'booking_time' => $booking_data['booking_time'],
        'service_address' => $booking_data['service_address'],
        'customer_notes' => $booking_data['customer_notes'] ?? '',
        'duration' => isset($booking_data['duration']) ? (int)$booking_data['duration'] : 1,
        'estimated_price' => $amount,
        'payment_reference' => $reference
    ];
    
    // Custom callback for bookings
    $host = $_SERVER['HTTP_HOST'] ?? '';
    if (strpos($host, 'infinityfreeapp.com') !== false) {
        $callback_url = 'https://worknpay.infinityfreeapp.com/view/booking_callback.php';
    } else {
        $callback_url = APP_BASE_URL . '/view/booking_callback.php';
    }
    
    // Initialize transaction with custom callback
    $data = [
        'amount' => ghs_to_pesewas($amount),
        'email' => $email,
        'reference' => $reference,
        'callback_url' => $callback_url,
        'currency' => 'GHS',
        'metadata' => [
            'app' => 'WorkNPay',
            'type' => 'booking',
            'customer_id' => $customer_id,
            'worker_id' => $booking_data['worker_id']
        ]
    ];
    
    $response = paystack_api_request('POST', PAYSTACK_INIT_ENDPOINT, $data);
    
    if (isset($response['status']) && $response['status'] === true) {
        $_SESSION['paystack_ref'] = $reference;
        $_SESSION['paystack_amount'] = $amount;
        
        echo json_encode([
            'status' => 'success',
            'authorization_url' => $response['data']['authorization_url'],
            'reference' => $reference
        ]);
    } else {
        throw new Exception($response['message'] ?? 'Payment initialization failed');
    }
    
} catch (Exception $e) {
    error_log("Booking payment init error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
