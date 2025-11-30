<?php
/**
 * Create Worker Profile Action
 * Creates a worker profile for newly registered workers
 */

header('Content-Type: application/json');

require_once '../settings/core.php';
require_once '../settings/db_class.php';

// Check if user is logged in and is a worker
require_login();
if (!is_worker()) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Access denied. Workers only.'
    ]);
    exit();
}

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (!isset($input['skills']) || !isset($input['bio']) || 
    !isset($input['experience_years']) || !isset($input['hourly_rate'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'All required fields must be filled'
    ]);
    exit();
}

$worker_id = get_user_id();
$skills = trim($input['skills']);
$bio = trim($input['bio']);
$experience_years = (int)$input['experience_years'];
$hourly_rate = (float)$input['hourly_rate'];

// Validate data
if (strlen($bio) < 50) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Bio must be at least 50 characters'
    ]);
    exit();
}

if ($hourly_rate < 10) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Hourly rate must be at least GH₵10'
    ]);
    exit();
}

// Check if profile already exists
$db = new db_connection();
$check_query = "SELECT user_id FROM worker_profiles WHERE user_id = $worker_id";
$existing = $db->db_fetch_one($check_query);

if ($existing) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Worker profile already exists'
    ]);
    exit();
}

// Escape data for SQL
$skills = mysqli_real_escape_string($db->db_conn(), $skills);
$bio = mysqli_real_escape_string($db->db_conn(), $bio);

// Insert worker profile (service_areas field doesn't exist in schema, so we'll skip it)
$insert_query = "INSERT INTO worker_profiles 
                 (user_id, skills, bio, experience_years, hourly_rate, 
                  average_rating, total_jobs_completed, available_balance, verification_badge, created_at) 
                 VALUES 
                 ($worker_id, '$skills', '$bio', $experience_years, $hourly_rate, 
                  0, 0, 0, 0, NOW())";

if ($db->db_query($insert_query)) {
    error_log("Worker profile created for user #$worker_id");
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Worker profile created successfully'
    ]);
} else {
    error_log("Failed to create worker profile for user #$worker_id");
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to create worker profile'
    ]);
}
?>
