<?php
/**
 * Get Payout History Action
 * Returns worker's payout history for transaction modal
 */

header('Content-Type: application/json');

require_once '../settings/core.php';
require_once '../controllers/payout_controller.php';

// Check if user is logged in and is a worker
require_login();
if (!is_worker()) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Access denied. Workers only.'
    ]);
    exit();
}

$worker_id = get_user_id();

try {
    // Get payout history
    $payouts = get_payout_history_ctr($worker_id);
    
    if ($payouts === false) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to fetch payout history'
        ]);
        exit();
    }
    
    echo json_encode([
        'status' => 'success',
        'payouts' => $payouts
    ]);
    
} catch (Exception $e) {
    error_log("Error fetching payout history: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred while fetching payout history'
    ]);
}
?>
