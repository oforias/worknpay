<?php
/**
 * Delete Service Action
 * Allows workers to delete (deactivate) their service offerings
 */

header('Content-Type: application/json');

require_once '../settings/core.php';
require_once '../controllers/service_controller.php';

// Check authentication
require_login();

// Check if user is a worker
if (!is_worker()) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Only workers can delete services'
    ]);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

try {
    // Validate input
    if (!isset($input['service_id'])) {
        throw new Exception('Service ID is required');
    }
    
    $service_id = (int)$input['service_id'];
    $worker_id = get_user_id();
    
    // Check ownership
    if (!is_service_owner_ctr($service_id, $worker_id)) {
        throw new Exception('You can only delete your own services');
    }
    
    // Delete service (soft delete)
    if (delete_service_ctr($service_id)) {
        error_log("Service #$service_id deleted by worker #$worker_id");
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Service deleted successfully'
        ]);
    } else {
        throw new Exception('Failed to delete service. Please try again.');
    }
    
} catch (Exception $e) {
    error_log("Service deletion error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
