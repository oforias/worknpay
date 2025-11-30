<?php
/**
 * User Class
 * Handles all user-related database operations
 */

require_once(__DIR__ . '/../settings/db_class.php');

class User extends db_connection
{
    /**
     * Register a new user
     * 
     * @param string $name User's full name
     * @param string $email User's email address
     * @param string $password User's password (will be hashed)
     * @param string $phone User's phone number
     * @param int $role User role (1=Customer, 2=Worker, 3=Admin)
     * @param string $country User's country (default: Ghana)
     * @param string $city User's city
     * @param string $address User's address
     * @return int|false User ID if successful, false otherwise
     */
    public function register_user($name, $email, $password, $phone, $role = 1, $country = 'Ghana', $city = null, $address = null)
    {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Escape inputs
        $name = $this->db_escape($name);
        $email = $this->db_escape($email);
        $phone = $this->db_escape($phone);
        $country = $this->db_escape($country);
        $city = $city ? $this->db_escape($city) : 'NULL';
        $address = $address ? "'" . $this->db_escape($address) . "'" : 'NULL';
        
        // Build query
        $sql = "INSERT INTO users (user_name, user_email, user_password, user_phone, user_role, user_country, user_city, user_address) 
                VALUES ('$name', '$email', '$hashed_password', '$phone', $role, '$country', " . 
                ($city !== 'NULL' ? "'$city'" : 'NULL') . ", $address)";
        
        // Execute query
        if ($this->db_write_query($sql)) {
            return $this->last_insert_id();
        }
        
        return false;
    }
    
    /**
     * Check if email already exists
     * 
     * @param string $email Email to check
     * @return bool True if email exists, false otherwise
     */
    public function email_exists($email)
    {
        $email = $this->db_escape($email);
        $sql = "SELECT user_id FROM users WHERE user_email = '$email' LIMIT 1";
        
        return $this->db_fetch_one($sql) !== false;
    }
    
    /**
     * Login user
     * 
     * @param string $email User's email
     * @param string $password User's password
     * @return array|false User data if successful, false otherwise
     */
    public function login_user($email, $password)
    {
        $email = $this->db_escape($email);
        $sql = "SELECT user_id, user_name, user_email, user_password, user_role, user_phone, is_active 
                FROM users 
                WHERE user_email = '$email' 
                LIMIT 1";
        
        $user = $this->db_fetch_one($sql);
        
        if ($user && password_verify($password, $user['user_password'])) {
            // Check if user is active
            if ($user['is_active'] == 0) {
                error_log("Login attempt for inactive user: $email");
                return false;
            }
            
            // Remove password from returned data
            unset($user['user_password']);
            return $user;
        }
        
        return false;
    }
    
    /**
     * Get user by ID
     * 
     * @param int $user_id User ID
     * @return array|false User data if found, false otherwise
     */
    public function get_user_by_id($user_id)
    {
        $user_id = (int) $user_id;
        $sql = "SELECT user_id, user_name, user_email, user_phone, user_role, user_country, user_city, 
                       user_address, user_image, is_verified, is_active, created_at 
                FROM users 
                WHERE user_id = $user_id 
                LIMIT 1";
        
        return $this->db_fetch_one($sql);
    }
    
    /**
     * Update user profile
     * 
     * @param int $user_id User ID
     * @param array $data Associative array of fields to update
     * @return bool True if successful, false otherwise
     */
    public function update_user($user_id, $data)
    {
        $user_id = (int) $user_id;
        $updates = [];
        
        // Build update string
        foreach ($data as $field => $value) {
            $escaped_value = $this->db_escape($value);
            $updates[] = "$field = '$escaped_value'";
        }
        
        if (empty($updates)) {
            return false;
        }
        
        $update_string = implode(', ', $updates);
        $sql = "UPDATE users SET $update_string WHERE user_id = $user_id";
        
        return $this->db_write_query($sql);
    }
    
    /**
     * Change user password
     * 
     * @param int $user_id User ID
     * @param string $new_password New password (will be hashed)
     * @return bool True if successful, false otherwise
     */
    public function change_password($user_id, $new_password)
    {
        $user_id = (int) $user_id;
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        $sql = "UPDATE users SET user_password = '$hashed_password' WHERE user_id = $user_id";
        
        return $this->db_write_query($sql);
    }
    
    /**
     * Deactivate user account
     * 
     * @param int $user_id User ID
     * @return bool True if successful, false otherwise
     */
    public function deactivate_user($user_id)
    {
        $user_id = (int) $user_id;
        $sql = "UPDATE users SET is_active = 0 WHERE user_id = $user_id";
        
        return $this->db_write_query($sql);
    }
    
    /**
     * Activate user account
     * 
     * @param int $user_id User ID
     * @return bool True if successful, false otherwise
     */
    public function activate_user($user_id)
    {
        $user_id = (int) $user_id;
        $sql = "UPDATE users SET is_active = 1 WHERE user_id = $user_id";
        
        return $this->db_write_query($sql);
    }
}
?>
