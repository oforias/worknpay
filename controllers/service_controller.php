<?php
/**
 * Service Controller
 * Business logic for service management
 */

require_once(__DIR__ . '/../classes/service_class.php');

/**
 * Create a new service
 */
function create_service_ctr($worker_id, $name, $category_id, $description, $price, $duration = null)
{
    $service = new Service();
    return $service->create_service($worker_id, $name, $category_id, $description, $price, $duration);
}

/**
 * Get service details by ID
 */
function get_service_details_ctr($service_id)
{
    $service = new Service();
    return $service->get_service_by_id($service_id);
}

/**
 * Get all services for a worker
 */
function get_worker_services_ctr($worker_id, $active_only = true)
{
    $service = new Service();
    return $service->get_worker_services($worker_id, $active_only);
}

/**
 * Update service
 */
function update_service_ctr($service_id, $name, $category_id, $description, $price, $duration = null)
{
    $service = new Service();
    return $service->update_service($service_id, $name, $category_id, $description, $price, $duration);
}

/**
 * Delete service (soft delete)
 */
function delete_service_ctr($service_id)
{
    $service = new Service();
    return $service->delete_service($service_id);
}

/**
 * Check if worker owns service
 */
function is_service_owner_ctr($service_id, $worker_id)
{
    $service = new Service();
    return $service->is_service_owner($service_id, $worker_id);
}

/**
 * Search services with filters
 */
function search_services_ctr($keyword = null, $filters = [])
{
    $service = new Service();
    
    $category_id = isset($filters['category_id']) ? $filters['category_id'] : null;
    $min_price = isset($filters['min_price']) ? $filters['min_price'] : null;
    $max_price = isset($filters['max_price']) ? $filters['max_price'] : null;
    $limit = isset($filters['limit']) ? $filters['limit'] : 20;
    $offset = isset($filters['offset']) ? $filters['offset'] : 0;
    
    return $service->search_services($keyword, $category_id, $min_price, $max_price, $limit, $offset);
}

/**
 * Get service analytics
 */
function get_service_analytics_ctr($service_id)
{
    $service = new Service();
    $stats = $service->get_service_stats($service_id);
    $trend = $service->get_service_trend($service_id, 30);
    
    return [
        'stats' => $stats,
        'trend' => $trend
    ];
}

/**
 * Get all service categories
 */
function get_service_categories_ctr()
{
    $service = new Service();
    return $service->get_categories_with_counts();
}
?>

/**
 * Get all service categories
 */
function get_all_categories_ctr()
{
    $service = new Service();
    return $service->get_all_categories();
}
