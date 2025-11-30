<?php
/**
 * Submit Review
 * Allows customers to rate and review workers after service completion
 */

header('Content-Type: application/json');

require_once '../settings/core.php';
require_once '../settings/db_class.php';

// Check authentication
if (!is_logged_in()) {
    echo json_encode(['status' => 'error', 'message' => 'Authentication required']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$booking_id = isset($input['booking_id']) ? (int)$input['booking_id'] : 0;
$worker_id = isset($input['worker_id']) ? (int)$input['worker_id'] : 0;
$rating = isset($input['rating']) ? (int)$input['rating'] : 0;
$review = isset($input['review']) ? trim($input['review']) : '';

// Validate inputs
if ($booking_id <= 0 || $worker_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid booking or worker ID']);
    exit();
}

if ($rating < 1 || $rating > 5) {
    echo json_encode(['status' => 'error', 'message' => 'Rating must be between 1 and 5']);
    exit();
}

try {
    $customer_id = get_user_id();
    $db = new db_connection();
    
    // Log the attempt
    error_log("Review submission attempt - Booking: $booking_id, Worker: $worker_id, Customer: $customer_id, Rating: $rating");
    
    // Verify booking belongs to customer and is completed
    $booking_query = "SELECT booking_id, customer_id, booking_status, worker_id
                      FROM bookings 
                      WHERE booking_id = $booking_id 
                      AND customer_id = $customer_id 
                      AND booking_status = 'completed'";
    $booking = $db->db_fetch_one($booking_query);
    
    if (!$booking) {
        error_log("Review error: Booking not found or not completed. Booking ID: $booking_id, Customer ID: $customer_id");
        throw new Exception('Invalid booking or booking not completed');
    }
    
    // Verify worker_id matches
    if ($booking['worker_id'] != $worker_id) {
        error_log("Review error: Worker ID mismatch. Expected: " . $booking['worker_id'] . ", Got: $worker_id");
        throw new Exception('Worker ID mismatch');
    }
    
    // Check if already reviewed
    $check_query = "SELECT review_id FROM reviews WHERE booking_id = $booking_id";
    $existing = $db->db_fetch_one($check_query);
    
    if ($existing) {
        error_log("Review error: Booking already reviewed. Review ID: " . $existing['review_id']);
        throw new Exception('You have already reviewed this booking');
    }
    
    // Escape review text
    $review_escaped = $db->db_escape($review);
    
    // Insert review
    $insert_query = "INSERT INTO reviews (
                        booking_id, 
                        worker_id, 
                        customer_id, 
                        rating, 
                        review_text, 
                        created_at
                     ) VALUES (
                        $booking_id, 
                        $worker_id, 
                        $customer_id, 
                        $rating, 
                        '$review_escaped', 
                        NOW()
                     )";
    
    error_log("Executing insert query: $insert_query");
    
    if (!$db->db_query($insert_query)) {
        error_log("Review insert failed - Query: $insert_query");
        throw new Exception('Failed to save review');
    }
    
    error_log("Review inserted successfully");
    
    // Update worker's average rating
    $update_rating_query = "UPDATE worker_profiles wp
                            SET average_rating = (
                                SELECT AVG(rating) 
                                FROM reviews 
                                WHERE worker_id = $worker_id
                            )
                            WHERE user_id = $worker_id";
    
    $db->db_query($update_rating_query);
    error_log("Worker rating updated for worker_id: $worker_id");
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Review submitted successfully'
    ]);
    
} catch (Exception $e) {
    error_log("Submit review error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
