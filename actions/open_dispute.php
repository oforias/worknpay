<?php
/**
 * Open Dispute Action
 * Allows customers to open disputes on completed bookings
 */

header('Content-Type: application/json');

require_once '../settings/core.php';
require_once '../controllers/dispute_controller.php';
require_once '../controllers/booking_controller.php';

// Check authentication
require_login();

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

try {
    // Validate input
    if (!isset($input['booking_id']) || !isset($input['reason']) || !isset($input['description'])) {
        throw new Exception('Booking ID, reason, and description are required');
    }
    
    $booking_id = (int)$input['booking_id'];
    $reason = trim($input['reason']);
    $description = trim($input['description']);
    $user_id = get_user_id();
    
    // Validate reason
    $valid_reasons = ['service_not_completed', 'poor_quality', 'overcharged', 'damaged_property', 'other'];
    if (!in_array($reason, $valid_reasons)) {
        throw new Exception('Invalid dispute reason');
    }
    
    // Get booking details
    $booking = get_booking_by_id_ctr($booking_id);
    
    if (!$booking) {
        throw new Exception('Booking not found');
    }
    
    // Verify user is customer on this booking
    if ($booking['customer_id'] != $user_id) {
        throw new Exception('Unauthorized: You can only dispute your own bookings');
    }
    
    // Verify booking is completed
    if ($booking['booking_status'] !== 'completed') {
        throw new Exception('Only completed bookings can be disputed');
    }
    
    // Check if payment record exists
    require_once '../classes/payment_class.php';
    $payment_class = new payment_class();
    $payment = $payment_class->get_payment_by_booking($booking_id);
    
    if (!$payment) {
        throw new Exception('No payment record found for this booking. Only paid bookings can be disputed.');
    }
    
    // Check if payment is in a valid state for disputes
    if ($payment['payment_status'] === 'refunded') {
        throw new Exception('This booking has already been refunded and cannot be disputed.');
    }
    
    // Check eligibility (48-hour window, no existing dispute)
    $eligibility = validate_dispute_eligibility_ctr($booking_id);
    if (!$eligibility['eligible']) {
        throw new Exception($eligibility['reason']);
    }
    
    // Create dispute
    if (open_dispute_ctr($booking_id, $booking['customer_id'], $booking['worker_id'], $reason, $description)) {
        // Update payment to prevent auto-release
        require_once '../settings/db_class.php';
        $db = new db_connection();
        $update_payment = "UPDATE payments 
                          SET auto_release_date = NULL 
                          WHERE booking_id = $booking_id 
                          AND escrow_status = 'held'";
        $db->db_query($update_payment);
        
        error_log("Dispute opened for booking #$booking_id by customer #$user_id");
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Dispute opened successfully. An admin will review your case within 24-48 hours.'
        ]);
    } else {
        throw new Exception('Failed to create dispute. Please try again.');
    }
    
} catch (Exception $e) {
    error_log("Dispute creation error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
