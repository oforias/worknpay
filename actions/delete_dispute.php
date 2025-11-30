<?php
/**
 * Delete/Cancel Dispute Action
 * Admin only - allows deletion of disputes
 */

header('Content-Type: application/json');

require_once '../settings/core.php';
require_once '../controllers/dispute_controller.php';

// Check authentication
require_login();

// Admin only
if (!is_admin()) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized. Admin access required.']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$dispute_id = isset($input['dispute_id']) ? (int)$input['dispute_id'] : 0;

if ($dispute_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid dispute ID']);
    exit();
}

try {
    $result = delete_dispute_ctr($dispute_id);
    
    if ($result) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Dispute deleted successfully'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to delete dispute or dispute is already resolved'
        ]);
    }
    
} catch (Exception $e) {
    error_log("Delete dispute error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
