<?php
/**
 * Update Worker Profile
 * Updates worker's professional information
 */

require_once '../settings/core.php';
require_once '../settings/db_class.php';

header('Content-Type: application/json');

// Check if user is logged in and is a worker
if (!is_logged_in() || !is_worker()) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Unauthorized access'
    ]);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid input'
    ]);
    exit();
}

$worker_id = get_user_id();
$skills = isset($input['skills']) ? trim($input['skills']) : '';
$bio = isset($input['bio']) ? trim($input['bio']) : '';
$experience_years = isset($input['years_experience']) ? intval($input['years_experience']) : 0;
$hourly_rate = isset($input['hourly_rate']) ? floatval($input['hourly_rate']) : 0;

$db = new db_connection();

try {
    $skills_escaped = mysqli_real_escape_string($db->db_conn(), $skills);
    $bio_escaped = mysqli_real_escape_string($db->db_conn(), $bio);
    
    $query = "UPDATE worker_profiles 
              SET skills = '$skills_escaped',
                  bio = '$bio_escaped',
                  experience_years = $experience_years,
                  hourly_rate = $hourly_rate
              WHERE user_id = $worker_id";
    
    if ($db->db_query($query)) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Professional profile updated successfully'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to update profile'
        ]);
    }
} catch (Exception $e) {
    error_log("Update worker profile error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred while updating profile'
    ]);
}
?>
