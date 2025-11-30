<?php
/**
 * Create Service Action
 * Allows workers to create new service offerings
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
        'message' => 'Only workers can create services'
    ]);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

try {
    // Validate input
    if (!isset($input['service_name']) || !isset($input['category_id']) || 
        !isset($input['service_description']) || !isset($input['base_price'])) {
        throw new Exception('Service name, category, description, and price are required');
    }
    
    $worker_id = get_user_id();
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
    
    if (empty($description)) {
        throw new Exception('Service description is required');
    }
    
    if ($price <= 0) {
        throw new Exception('Price must be greater than 0');
    }
    
    // Valid category IDs (1-4)
    if ($category_id < 1 || $category_id > 4) {
        throw new Exception('Invalid service category');
    }
    
    // Create service
    if (create_service_ctr($worker_id, $name, $category_id, $description, $price, $duration)) {
        error_log("Service created by worker #$worker_id: $name");
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Service created successfully'
        ]);
    } else {
        throw new Exception('Failed to create service. Please try again.');
    }
    
} catch (Exception $e) {
    error_log("Service creation error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
