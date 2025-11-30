<?php
/**
 * Create Booking Action
 * Creates a booking after successful payment verification
 */

header('Content-Type: application/json');

require_once '../settings/core.php';
require_once '../controllers/booking_controller.php';

// Check if user is logged in
if (!is_logged_in()) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Please login to create a booking'
    ]);
    exit();
}

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);

// Validate required fields
$required = ['worker_id', 'booking_date', 'booking_time', 'service_address', 'estimated_price', 'payment_reference'];
foreach ($required as $field) {
    if (!isset($input[$field]) || empty($input[$field])) {
        echo json_encode([
            'status' => 'error',
            'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required'
        ]);
        exit();
    }
}

$customer_id = get_user_id();
$worker_id = (int) $input['worker_id'];
$service_id = isset($input['service_id']) ? (int) $input['service_id'] : null; // NULL if not provided
$booking_date = $input['booking_date'];
$booking_time = $input['booking_time'];
$service_address = $input['service_address'];
$estimated_price = (float) $input['estimated_price'];
$customer_notes = isset($input['customer_notes']) ? $input['customer_notes'] : null;
$payment_reference = $input['payment_reference'];

try {
    // Create booking
    $result = create_booking_ctr(
        $customer_id,
        $worker_id,
        $service_id,
        $booking_date,
        $booking_time,
        $service_address,
        $estimated_price,
        $customer_notes
    );
    
    if ($result) {
        error_log("Booking created - ID: {$result['booking_id']}, Reference: {$result['booking_reference']}, Payment: $payment_reference");
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Booking created successfully',
            'booking_id' => $result['booking_id'],
            'booking_reference' => $result['booking_reference']
        ]);
    } else {
        throw new Exception('Failed to create booking in database');
    }
    
} catch (Exception $e) {
    error_log("Booking creation error: " . $e->getMessage());
    
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to create booking: ' . $e->getMessage()
    ]);
}
?>
