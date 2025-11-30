<?php
/**
 * Login Action Handler
 * Processes user login requests
 */

session_start();

require_once '../settings/core.php';
require_once '../controllers/user_controller.php';

// Get POST data (handle both form and JSON)
if ($_SERVER['CONTENT_TYPE'] === 'application/json') {
    $input = json_decode(file_get_contents('php://input'), true);
} else {
    $input = $_POST;
}

// Validate required fields
if (!isset($input['email']) || !isset($input['password']) || empty(trim($input['email'])) || empty($input['password'])) {
    header('Location: ../view/login.php?error=empty_fields');
    exit();
}

$email = trim($input['email']);
$password = $input['password'];

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: ../view/login.php?error=invalid_credentials');
    exit();
}

// Attempt login
$user = login_user_ctr($email, $password);

if ($user) {
    // Set session variables
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['user_name'] = $user['user_name'];
    $_SESSION['user_email'] = $user['user_email'];
    $_SESSION['user_role'] = $user['user_role'];
    $_SESSION['user_phone'] = $user['user_phone'];
    
    // Log successful login
    error_log("User logged in - ID: {$user['user_id']}, Email: $email, Role: {$user['user_role']}");
    
    // Redirect based on role (system automatically knows from database)
    if ($user['user_role'] == 2) {
        // Worker
        header('Location: ../view/worker_dashboard_new.php');
    } elseif ($user['user_role'] == 1) {
        // Customer
        header('Location: ../view/home.php');
    } elseif ($user['user_role'] == 3) {
        // Admin
        header('Location: ../view/admin_dashboard.php');
    } else {
        // Unknown role, redirect to home
        header('Location: ../view/home.php');
    }
    exit();
} else {
    // Log failed login attempt
    error_log("Failed login attempt for email: $email");
    
    header('Location: ../view/login.php?error=invalid_credentials');
    exit();
}
?>
