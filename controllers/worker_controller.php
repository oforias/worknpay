<?php
/**
 * Worker Controller
 * Business logic for worker operations
 */

require_once(__DIR__ . '/../settings/db_class.php');

/**
 * Get workers by category/skill
 * 
 * @param string $category Category or skill to filter by
 * @param int $limit Maximum number of results
 * @return array Array of worker data
 */
function get_workers_by_category_ctr($category = null, $limit = 20) {
    $db = new db_connection();
    
    $sql = "SELECT u.user_id, u.user_name, u.user_city, u.user_phone,
            wp.bio, wp.skills, wp.hourly_rate, wp.average_rating, 
            wp.total_jobs_completed, wp.verification_badge, wp.experience_years
            FROM users u
            INNER JOIN worker_profiles wp ON u.user_id = wp.user_id
            WHERE u.user_role = 2 
            AND u.is_active = 1
            AND wp.is_available = 1";
    
    // Add category filter if provided
    if ($category) {
        $category_escaped = $db->db_escape($category);
        $sql .= " AND (wp.skills LIKE '%$category_escaped%' 
                  OR wp.bio LIKE '%$category_escaped%')";
    }
    
    // Order by rating and jobs completed
    $sql .= " ORDER BY wp.average_rating DESC, wp.total_jobs_completed DESC";
    
    // Add limit
    $limit = (int)$limit;
    $sql .= " LIMIT $limit";
    
    return $db->db_fetch_all($sql);
}

/**
 * Search workers by keyword
 * 
 * @param string $keyword Search keyword
 * @param int $limit Maximum number of results
 * @return array Array of worker data
 */
function search_workers_ctr($keyword, $limit = 20) {
    $db = new db_connection();
    
    $keyword_escaped = $db->db_escape($keyword);
    
    $sql = "SELECT u.user_id, u.user_name, u.user_city, u.user_phone,
            wp.bio, wp.skills, wp.hourly_rate, wp.average_rating, 
            wp.total_jobs_completed, wp.verification_badge, wp.experience_years
            FROM users u
            INNER JOIN worker_profiles wp ON u.user_id = wp.user_id
            WHERE u.user_role = 2 
            AND u.is_active = 1
            AND wp.is_available = 1
            AND (u.user_name LIKE '%$keyword_escaped%'
                 OR wp.skills LIKE '%$keyword_escaped%'
                 OR wp.bio LIKE '%$keyword_escaped%'
                 OR u.user_city LIKE '%$keyword_escaped%')
            ORDER BY wp.average_rating DESC, wp.total_jobs_completed DESC
            LIMIT $limit";
    
    return $db->db_fetch_all($sql);
}

/**
 * Get worker by ID
 * 
 * @param int $worker_id Worker user ID
 * @return array|false Worker data or false
 */
function get_worker_by_id_ctr($worker_id) {
    $db = new db_connection();
    
    $worker_id = (int)$worker_id;
    
    $sql = "SELECT u.user_id, u.user_name, u.user_email, u.user_city, u.user_phone,
            wp.bio, wp.skills, wp.hourly_rate, wp.average_rating, 
            wp.total_jobs_completed, wp.verification_badge, wp.experience_years,
            wp.is_available
            FROM users u
            INNER JOIN worker_profiles wp ON u.user_id = wp.user_id
            WHERE u.user_id = $worker_id 
            AND u.user_role = 2 
            AND u.is_active = 1";
    
    return $db->db_fetch_one($sql);
}

/**
 * Get all available service categories
 * 
 * @return array Array of unique categories
 */
function get_service_categories_ctr() {
    $db = new db_connection();
    
    $sql = "SELECT DISTINCT wp.skills
            FROM worker_profiles wp
            INNER JOIN users u ON wp.user_id = u.user_id
            WHERE u.user_role = 2 
            AND u.is_active = 1
            AND wp.is_available = 1
            AND wp.skills IS NOT NULL
            AND wp.skills != ''
            ORDER BY wp.skills ASC";
    
    $results = $db->db_fetch_all($sql);
    
    // Extract unique skills/categories
    $categories = [];
    foreach ($results as $row) {
        if (!empty($row['skills'])) {
            // Split by comma if multiple skills
            $skills = array_map('trim', explode(',', $row['skills']));
            $categories = array_merge($categories, $skills);
        }
    }
    
    // Remove duplicates and return
    return array_unique($categories);
}
?>
