<?php
/**
 * Respond to Dispute Action
 * Allows workers to respond to disputes filed against them
 */

header('Content-Type: application/json');

require_once '../settings/core.php';
require_once '../controllers/dispute_controller.php';

// Check authentication
require_login();

// Check if user is a worker
if (!is_worker()) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Only workers can respond to disputes'
    ]);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

try {
    // Validate input
    if (!isset($input['dispute_id']) || !isset($input['response'])) {
        throw new Exception('Dispute ID and response are required');
    }
    
    $dispute_id = (int)$input['dispute_id'];
    $response = trim($input['response']);
    $worker_id = get_user_id();
    
    // Validate response
    if (empty($response)) {
        throw new Exception('Response cannot be empty');
    }
    
    // Get dispute details to verify worker ownership
    $dispute = get_dispute_details_ctr($dispute_id);
    
    if (!$dispute) {
        throw new Exception('Dispute not found');
    }
    
    // Verify this worker is the one being disputed
    if ($dispute['worker_id'] != $worker_id) {
        throw new Exception('Unauthorized: You can only respond to disputes filed against you');
    }
    
    // Check if dispute is still open
    if ($dispute['dispute_status'] !== 'open') {
        throw new Exception('This dispute has already been resolved');
    }
    
    // Check if worker already responded
    if (!empty($dispute['worker_response'])) {
        throw new Exception('You have already responded to this dispute');
    }
    
    // Add worker response
    if (add_worker_response_ctr($dispute_id, $response)) {
        error_log("Worker #$worker_id responded to dispute #$dispute_id");
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Response submitted successfully. An admin will review both sides and make a decision.'
        ]);
    } else {
        throw new Exception('Failed to submit response. Please try again.');
    }
    
} catch (Exception $e) {
    error_log("Dispute response error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
