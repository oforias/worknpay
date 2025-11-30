<?php
/**
 * Change Password
 * Allows users to change their password
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

if (!$input || !isset($input['current_password']) || !isset($input['new_password'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Current password and new password are required'
    ]);
    exit();
}

$user_id = get_user_id();
$current_password = $input['current_password'];
$new_password = $input['new_password'];

// Validate new password length
if (strlen($new_password) < 6) {
    echo json_encode([
        'status' => 'error',
        'message' => 'New password must be at least 6 characters long'
    ]);
    exit();
}

$db = new db_connection();

try {
    // Get current password hash
    $query = "SELECT user_password FROM users WHERE user_id = $user_id";
    $user = $db->db_fetch_one($query);
    
    if (!$user) {
        echo json_encode([
            'status' => 'error',
            'message' => 'User not found'
        ]);
        exit();
    }
    
    // Verify current password
    if (!password_verify($current_password, $user['user_password'])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Current password is incorrect'
        ]);
        exit();
    }
    
    // Hash new password
    $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
    
    // Update password
    $update_query = "UPDATE users 
                     SET user_password = '" . mysqli_real_escape_string($db->db_conn(), $new_password_hash) . "'
                     WHERE user_id = $user_id";
    
    if ($db->db_query($update_query)) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Password changed successfully'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to change password'
        ]);
    }
} catch (Exception $e) {
    error_log("Change password error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred while changing password'
    ]);
}
?>
