<?php
/**
 * Delete/Cancel Dispute Action
 * Allows customers or admins to cancel open disputes
 */

header('Content-Type: application/json');

require_once '../settings/core.php';
require_once '../controllers/dispute_controller.php';

// Check authentication
require_login();

$input = json_decode(file_get_contents('php://input'), true);
$dispute_id = isset($input['dispute_id']) ? (int)$input['dispute_id'] : 0;

if ($dispute_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid dispute ID']);
    exit();
}

try {
    $user_id = get_user_id();
    
    $result = delete_dispute_ctr($dispute_id, $user_id);
    
    if ($result['success']) {
        echo json_encode([
            'status' => 'success',
            'message' => $result['message']
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => $result['message']
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
