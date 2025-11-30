<?php
/**
 * Cancel Booking Action
 * Allows customers to cancel pending bookings
 */

header('Content-Type: application/json');

require_once '../settings/core.php';
require_once '../controllers/booking_controller.php';

// Check authentication
require_login();

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

try {
    if (!isset($input['booking_id'])) {
        throw new Exception('Booking ID is required');
    }
    
    $booking_id = (int)$input['booking_id'];
    $user_id = get_user_id();
    
    // Get booking details
    $booking = get_booking_by_id_ctr($booking_id);
    
    if (!$booking) {
        throw new Exception('Booking not found');
    }
    
    // Verify user is customer on this booking
    if ($booking['customer_id'] != $user_id) {
        throw new Exception('Unauthorized: You can only cancel your own bookings');
    }
    
    // Verify booking is pending
    if ($booking['booking_status'] !== 'pending') {
        throw new Exception('Only pending bookings can be cancelled. This booking is already ' . $booking['booking_status']);
    }
    
    // Cancel booking
    if (cancel_booking_ctr($booking_id)) {
        // Process refund if payment exists
        require_once '../settings/db_class.php';
        $db = new db_connection();
        
        $payment_query = "SELECT * FROM payments WHERE booking_id = $booking_id";
        $payment = $db->db_fetch_one($payment_query);
        
        if ($payment && $payment['payment_status'] === 'successful') {
            // Update payment to refunded
            $update_payment = "UPDATE payments 
                              SET payment_status = 'refunded', 
                                  escrow_status = 'refunded' 
                              WHERE booking_id = $booking_id";
            $db->db_query($update_payment);
            
            error_log("Booking #$booking_id cancelled with refund for customer #$user_id");
            
            echo json_encode([
                'status' => 'success',
                'message' => 'Booking cancelled successfully. Your payment will be refunded within 5-7 business days.'
            ]);
        } else {
            error_log("Booking #$booking_id cancelled (no payment) for customer #$user_id");
            
            echo json_encode([
                'status' => 'success',
                'message' => 'Booking cancelled successfully.'
            ]);
        }
    } else {
        throw new Exception('Failed to cancel booking. Please try again.');
    }
    
} catch (Exception $e) {
    error_log("Booking cancellation error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
