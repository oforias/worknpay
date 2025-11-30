<?php
/**
 * Update Personal Information
 * Updates user's name, phone, and email
 */

require_once '../settings/core.php';
require_once '../settings/db_class.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!is_logged_in()) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Unauthorized access'
    ]);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['user_name']) || !isset($input['user_email'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Name and email are required'
    ]);
    exit();
}

$user_id = get_user_id();
$user_name = trim($input['user_name']);
$user_email = trim($input['user_email']);
$user_phone = isset($input['user_phone']) ? trim($input['user_phone']) : '';
$user_city = isset($input['user_city']) ? trim($input['user_city']) : '';

// Validate inputs
if (empty($user_name) || empty($user_email)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Name and email are required'
    ]);
    exit();
}

// Validate email
if (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid email address'
    ]);
    exit();
}

// Validate phone if provided (Ghana format)
if (!empty($user_phone) && !preg_match('/^0\d{9}$/', $user_phone)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid phone number format. Use 10 digits starting with 0'
    ]);
    exit();
}

$db = new db_connection();

// Check if email is already used by another user
$email_check = "SELECT user_id FROM users WHERE user_email = '" . mysqli_real_escape_string($db->db_conn(), $user_email) . "' AND user_id != $user_id";
$existing_email = $db->db_fetch_one($email_check);

if ($existing_email) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Email address is already in use'
    ]);
    exit();
}

try {
    $user_name_escaped = mysqli_real_escape_string($db->db_conn(), $user_name);
    $user_email_escaped = mysqli_real_escape_string($db->db_conn(), $user_email);
    $user_phone_escaped = mysqli_real_escape_string($db->db_conn(), $user_phone);
    $user_city_escaped = mysqli_real_escape_string($db->db_conn(), $user_city);
    
    $query = "UPDATE users 
              SET user_name = '$user_name_escaped',
                  user_email = '$user_email_escaped',
                  user_phone = '$user_phone_escaped',
                  user_city = '$user_city_escaped'
              WHERE user_id = $user_id";
    
    if ($db->db_query($query)) {
        // Update session
        $_SESSION['user_name'] = $user_name;
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Personal information updated successfully'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to update information'
        ]);
    }
} catch (Exception $e) {
    error_log("Update personal info error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred while updating information'
    ]);
}
?>
