<?php
/**
 * Get Booking Details
 * Returns detailed information about a specific booking
 */

require_once '../settings/core.php';
require_once '../settings/db_class.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!is_logged_in()) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Unauthorized access'
    ]);
    exit();
}

if (!isset($_GET['booking_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Booking ID is required'
    ]);
    exit();
}

$booking_id = intval($_GET['booking_id']);
$user_id = get_user_id();
$db = new db_connection();

try {
    // Get booking details with customer info
    $query = "SELECT b.*, 
              u.user_name as customer_name,
              u.user_phone as customer_phone,
              u.user_email as customer_email
              FROM bookings b
              JOIN users u ON b.customer_id = u.user_id
              WHERE b.booking_id = $booking_id";
    
    // If worker, ensure they own this booking
    if (is_worker()) {
        $query .= " AND b.worker_id = $user_id";
    }
    // If customer, ensure they own this booking
    elseif (!is_admin()) {
        $query .= " AND b.customer_id = $user_id";
    }
    
    $booking = $db->db_fetch_one($query);
    
    if (!$booking) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Booking not found or access denied'
        ]);
        exit();
    }
    
    echo json_encode([
        'status' => 'success',
        'booking' => $booking
    ]);
    
} catch (Exception $e) {
    error_log("Get booking details error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to load booking details'
    ]);
}
?>
