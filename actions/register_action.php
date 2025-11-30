<?php
/**
 * Registration Action Handler
 * Processes user registration requests
 */

session_start();

require_once '../controllers/user_controller.php';

// Get POST data (handle both form and JSON)
if ($_SERVER['CONTENT_TYPE'] === 'application/json') {
    $input = json_decode(file_get_contents('php://input'), true);
} else {
    $input = $_POST;
}

// Map form field names to expected names
$name = isset($input['customer_name']) ? trim($input['customer_name']) : (isset($input['name']) ? trim($input['name']) : '');
$email = isset($input['customer_email']) ? trim($input['customer_email']) : (isset($input['email']) ? trim($input['email']) : '');
$phone = isset($input['customer_contact']) ? trim($input['customer_contact']) : (isset($input['phone']) ? trim($input['phone']) : '');
$password = isset($input['customer_pass']) ? $input['customer_pass'] : (isset($input['password']) ? $input['password'] : '');
$confirm_password = isset($input['confirm_password']) ? $input['confirm_password'] : '';
$role = isset($input['user_role']) ? (int) $input['user_role'] : (isset($input['role']) ? (int) $input['role'] : 1);
$city = isset($input['city']) ? trim($input['city']) : null;

// Validate required fields
if (empty($name) || empty($email) || empty($phone) || empty($password)) {
    header('Location: ../view/register.php?error=empty_fields');
    exit();
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: ../view/register.php?error=invalid_email');
    exit();
}

// Validate password match
if (!empty($confirm_password) && $password !== $confirm_password) {
    header('Location: ../view/register.php?error=password_mismatch');
    exit();
}

// Validate password length
if (strlen($password) < 6) {
    header('Location: ../view/register.php?error=password_short');
    exit();
}

// Validate role
if (!in_array($role, [1, 2])) {
    header('Location: ../view/register.php?error=invalid_role');
    exit();
}

// Check if email already exists
if (email_exists_ctr($email)) {
    header('Location: ../view/register.php?error=email_exists');
    exit();
}

// Register user
$user_id = register_user_ctr($name, $email, $password, $phone, $role, 'Ghana', $city);

if ($user_id) {
    error_log("New user registered - ID: $user_id, Email: $email, Role: $role");
    
    // Redirect to login with success message
    header('Location: ../view/login.php?message=registration_success');
    exit();
} else {
    error_log("Registration failed for email: $email");
    
    header('Location: ../view/register.php?error=registration_failed');
    exit();
}
?>
