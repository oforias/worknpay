<?php
/**
 * Service Class
 * Handles service catalog operations for workers
 */

require_once(__DIR__ . '/../settings/db_class.php');

class Service extends db_connection
{
    /**
     * Create a new service
     */
    public function create_service($worker_id, $name, $category_id, $description, $price, $duration = null)
    {
        $worker_id = (int)$worker_id;
        $name = $this->db_escape($name);
        $category_id = (int)$category_id;
        $description = $this->db_escape($description);
        $price = (float)$price;
        $duration = $duration ? (int)$duration : 'NULL';
        
        $sql = "INSERT INTO services 
                (worker_id, category_id, service_title, service_description, base_price, estimated_duration, is_active)
                VALUES 
                ($worker_id, $category_id, '$name', '$description', $price, $duration, 1)";
        
        return $this->db_query($sql);
    }
    
    /**
     * Get service by ID
     */
    public function get_service_by_id($service_id)
    {
        $service_id = (int)$service_id;
        
        $sql = "SELECT s.*, 
                sc.category_name,
                u.user_name as worker_name,
                wp.service_title as worker_service_title,
                wp.average_rating,
                wp.total_reviews
                FROM services s
                JOIN service_categories sc ON s.category_id = sc.category_id
                JOIN users u ON s.worker_id = u.user_id
                LEFT JOIN worker_profiles wp ON s.worker_id = wp.user_id
                WHERE s.service_id = $service_id";
        
        return $this->db_fetch_one($sql);
    }
    
    /**
     * Get all services for a worker
     */
    public function get_worker_services($worker_id, $active_only = true)
    {
        $worker_id = (int)$worker_id;
        $where = $active_only ? "AND s.is_active = 1" : "";
        
        $sql = "SELECT s.*,
                sc.category_name,
                COUNT(b.booking_id) as booking_count
                FROM services s
                JOIN service_categories sc ON s.category_id = sc.category_id
                LEFT JOIN bookings b ON s.service_id = b.service_id
                WHERE s.worker_id = $worker_id $where
                GROUP BY s.service_id
                ORDER BY s.created_at DESC";
        
        return $this->db_fetch_all($sql);
    }
    
    /**
     * Update service
     */
    public function update_service($service_id, $name, $category_id, $description, $price, $duration = null)
    {
        $service_id = (int)$service_id;
        $name = $this->db_escape($name);
        $category_id = (int)$category_id;
        $description = $this->db_escape($description);
        $price = (float)$price;
        $duration = $duration ? (int)$duration : 'NULL';
        
        $sql = "UPDATE services 
                SET service_title = '$name',
                    category_id = $category_id,
                    service_description = '$description',
                    base_price = $price,
                    estimated_duration = $duration
                WHERE service_id = $service_id";
        
        return $this->db_query($sql);
    }
    
    /**
     * Soft delete service (mark as inactive)
     */
    public function delete_service($service_id)
    {
        $service_id = (int)$service_id;
        
        $sql = "UPDATE services 
                SET is_active = 0
                WHERE service_id = $service_id";
        
        return $this->db_query($sql);
    }
    
    /**
     * Check if worker owns service
     */
    public function is_service_owner($service_id, $worker_id)
    {
        $service_id = (int)$service_id;
        $worker_id = (int)$worker_id;
        
        $sql = "SELECT service_id FROM services 
                WHERE service_id = $service_id 
                AND worker_id = $worker_id";
        
        $result = $this->db_fetch_one($sql);
        return $result !== false;
    }
    
    /**
     * Search services
     */
    public function search_services($keyword = null, $category_id = null, $min_price = null, $max_price = null, $limit = 20, $offset = 0)
    {
        $where = ["s.is_active = 1"];
        
        if ($keyword) {
            $keyword = $this->db_escape($keyword);
            $where[] = "(s.service_title LIKE '%$keyword%' OR s.service_description LIKE '%$keyword%' OR sc.category_name LIKE '%$keyword%')";
        }
        
        if ($category_id) {
            $category_id = (int)$category_id;
            $where[] = "s.category_id = $category_id";
        }
        
        if ($min_price !== null) {
            $min_price = (float)$min_price;
            $where[] = "s.base_price >= $min_price";
        }
        
        if ($max_price !== null) {
            $max_price = (float)$max_price;
            $where[] = "s.base_price <= $max_price";
        }
        
        $where_clause = implode(" AND ", $where);
        $limit = (int)$limit;
        $offset = (int)$offset;
        
        $sql = "SELECT s.*, 
                sc.category_name,
                u.user_name as worker_name,
                wp.service_title as worker_service_title,
                wp.average_rating,
                wp.total_reviews,
                wp.location
                FROM services s
                JOIN service_categories sc ON s.category_id = sc.category_id
                JOIN users u ON s.worker_id = u.user_id
                LEFT JOIN worker_profiles wp ON s.worker_id = wp.user_id
                WHERE $where_clause
                ORDER BY wp.average_rating DESC, s.created_at DESC
                LIMIT $limit OFFSET $offset";
        
        return $this->db_fetch_all($sql);
    }
    
    /**
     * Get service analytics/stats
     */
    public function get_service_stats($service_id)
    {
        $service_id = (int)$service_id;
        
        $sql = "SELECT 
                COUNT(b.booking_id) as total_bookings,
                SUM(CASE WHEN b.booking_status = 'completed' THEN 1 ELSE 0 END) as completed_bookings,
                SUM(CASE WHEN b.booking_status = 'completed' THEN b.estimated_price ELSE 0 END) as total_revenue,
                AVG(CASE WHEN r.rating IS NOT NULL THEN r.rating ELSE NULL END) as average_rating,
                COUNT(r.rating_id) as total_ratings
                FROM services s
                LEFT JOIN bookings b ON s.service_id = b.service_id
                LEFT JOIN ratings r ON b.booking_id = r.booking_id
                WHERE s.service_id = $service_id
                GROUP BY s.service_id";
        
        return $this->db_fetch_one($sql);
    }
    
    /**
     * Get booking trend for service (last 30 days)
     */
    public function get_service_trend($service_id, $days = 30)
    {
        $service_id = (int)$service_id;
        $days = (int)$days;
        
        $sql = "SELECT 
                DATE(b.booking_date) as date,
                COUNT(b.booking_id) as booking_count
                FROM bookings b
                WHERE b.service_id = $service_id
                AND b.booking_date >= DATE_SUB(NOW(), INTERVAL $days DAY)
                GROUP BY DATE(b.booking_date)
                ORDER BY date ASC";
        
        return $this->db_fetch_all($sql);
    }
    
    /**
     * Get all service categories
     */
    public function get_all_categories()
    {
        $sql = "SELECT * FROM service_categories 
                WHERE is_active = 1 
                ORDER BY category_name ASC";
        
        return $this->db_fetch_all($sql);
    }
    
    /**
     * Get service categories with counts
     */
    public function get_categories_with_counts()
    {
        $sql = "SELECT 
                sc.category_id,
                sc.category_name,
                COUNT(s.service_id) as service_count
                FROM service_categories sc
                LEFT JOIN services s ON sc.category_id = s.category_id AND s.is_active = 1
                WHERE sc.is_active = 1
                GROUP BY sc.category_id
                ORDER BY service_count DESC";
        
        return $this->db_fetch_all($sql);
    }
}
?>
