<?php
/**
 * Update Service Action
 * Allows workers to update their service offerings
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
        'message' => 'Only workers can update services'
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
        throw new Exception('You can only edit your own services');
    }
    
    $name = trim($input['service_name']);
    $category_id = (int)$input['category_id'];
    $description = trim($input['service_description']);
    $price = (float)$input['base_price'];
    $duration = isset($input['estimated_duration']) ? (int)$input['estimated_duration'] : null;
    
    // Validate data
    if (empty($name)) {
        throw new Exception('Service name is required');
    }
    
    if ($category_id <= 0) {
        throw new Exception('Please select a valid category');
    }
    
    if ($price <= 0) {
        throw new Exception('Price must be greater than 0');
    }
    
    // Update service
    if (update_service_ctr($service_id, $name, $category_id, $description, $price, $duration)) {
        error_log("Service #$service_id updated by worker #$worker_id");
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Service updated successfully'
        ]);
    } else {
        throw new Exception('Failed to update service. Please try again.');
    }
    
} catch (Exception $e) {
    error_log("Service update error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
