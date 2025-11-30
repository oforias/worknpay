<?php
/**
 * Update Booking Status Action
 * Allows workers to update booking status (accept, start, complete)
 */

header('Content-Type: application/json');

require_once '../settings/core.php';
require_once '../controllers/booking_controller.php';

// Check if user is logged in and is a worker
require_login();
if (!is_worker()) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Access denied. Workers only.'
    ]);
    exit();
}

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (!isset($input['booking_id']) || !isset($input['new_status'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Booking ID and new status are required'
    ]);
    exit();
}

$booking_id = (int)$input['booking_id'];
$new_status = trim($input['new_status']);
$worker_id = get_user_id();

// Validate status
$valid_statuses = ['accepted', 'in_progress', 'completed', 'rejected'];
if (!in_array($new_status, $valid_statuses)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid status'
    ]);
    exit();
}

// Update booking status using controller (includes ownership verification)
$result = update_booking_status_validated_ctr($booking_id, $new_status, $worker_id);

if ($result['success']) {
    // Get booking details for logging
    $booking = get_booking_by_id_ctr($booking_id);
    $current_status = $booking['booking_status'];
    
    // Log the status change
    error_log("Booking #$booking_id status changed to $new_status by worker #$worker_id");
    
    // If completed, update worker stats and set escrow release
    if ($new_status === 'completed') {
        require_once '../settings/db_class.php';
        $db = new db_connection();
        
        // Update worker stats (but don't add to balance yet - escrow handles that)
        $stats_query = "UPDATE worker_profiles 
                       SET total_jobs_completed = total_jobs_completed + 1
                       WHERE user_id = $worker_id";
        $db->db_query($stats_query);
        
        // Set auto-release date for escrow (24 hours from now)
        $release_query = "UPDATE payments 
                         SET auto_release_date = DATE_ADD(NOW(), INTERVAL 24 HOUR)
                         WHERE booking_id = $booking_id AND escrow_status = 'held'";
        $db->db_query($release_query);
        
        error_log("Escrow auto-release set for booking #$booking_id (24 hours from now)");
    }
    
    echo json_encode([
        'status' => 'success',
        'message' => $result['message'],
        'new_status' => $new_status
    ]);
} else {
    error_log("Failed to update booking #$booking_id status: " . $result['message']);
    echo json_encode([
        'status' => 'error',
        'message' => $result['message']
    ]);
}
?>
