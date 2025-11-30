<?php
/**
 * Logout Action Handler
 * Destroys user session and logs out
 */

require_once '../settings/core.php';

// Log the logout
if (is_logged_in()) {
    error_log("User logged out - ID: " . get_user_id() . ", Name: " . get_user_name());
}

// Destroy session
session_unset();
session_destroy();

// Redirect to login page
header('Location: ../view/login.php?message=logged_out');
exit();
?>
