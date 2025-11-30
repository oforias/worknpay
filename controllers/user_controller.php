<?php
/**
 * User Controller
 * Business logic for user operations
 */

require_once(__DIR__ . '/../classes/user_class.php');

/**
 * Register a new user
 */
function register_user_ctr($name, $email, $password, $phone, $role = 1, $country = 'Ghana', $city = null, $address = null)
{
    $user = new User();
    return $user->register_user($name, $email, $password, $phone, $role, $country, $city, $address);
}

/**
 * Check if email exists
 */
function email_exists_ctr($email)
{
    $user = new User();
    return $user->email_exists($email);
}

/**
 * Login user
 */
function login_user_ctr($email, $password)
{
    $user = new User();
    return $user->login_user($email, $password);
}

/**
 * Get user by ID
 */
function get_user_by_id_ctr($user_id)
{
    $user = new User();
    return $user->get_user_by_id($user_id);
}

/**
 * Update user profile
 */
function update_user_ctr($user_id, $data)
{
    $user = new User();
    return $user->update_user($user_id, $data);
}

/**
 * Change user password
 */
function change_password_ctr($user_id, $new_password)
{
    $user = new User();
    return $user->change_password($user_id, $new_password);
}

/**
 * Deactivate user
 */
function deactivate_user_ctr($user_id)
{
    $user = new User();
    return $user->deactivate_user($user_id);
}

/**
 * Activate user
 */
function activate_user_ctr($user_id)
{
    $user = new User();
    return $user->activate_user($user_id);
}
?>
