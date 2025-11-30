<?php
/**
 * Search Workers
 * API endpoint for searching workers by keyword or category
 */

header('Content-Type: application/json');

require_once '../settings/core.php';
require_once '../controllers/worker_controller.php';

// Check authentication
if (!is_logged_in()) {
    echo json_encode(['status' => 'error', 'message' => 'Authentication required']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$keyword = isset($input['keyword']) ? trim($input['keyword']) : '';
$category = isset($input['category']) ? trim($input['category']) : '';

try {
    $workers = [];
    
    if (!empty($keyword)) {
        // Search by keyword
        $workers = search_workers_ctr($keyword);
    } elseif (!empty($category)) {
        // Filter by category
        $workers = get_workers_by_category_ctr($category);
    } else {
        // Get all workers
        $workers = get_workers_by_category_ctr(null, 50);
    }
    
    echo json_encode([
        'status' => 'success',
        'workers' => $workers,
        'count' => count($workers)
    ]);
    
} catch (Exception $e) {
    error_log("Search workers error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to search workers'
    ]);
}
?>
