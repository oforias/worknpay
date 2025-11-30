<?php
require_once 'settings/db_class.php';

$db = new db_connection();

$password = 'password123';
$password_hash = password_hash($password, PASSWORD_DEFAULT);

echo "Resetting admin passwords...\n\n";

// Update all admin accounts
$update_query = "UPDATE users 
                 SET user_password = '" . mysqli_real_escape_string($db->db_conn(), $password_hash) . "' 
                 WHERE user_role = 3";

if ($db->db_query($update_query)) {
    echo "✅ Admin passwords reset successfully!\n\n";
    
    // Show admin accounts
    $admins = $db->db_fetch_all("SELECT user_id, user_name, user_email FROM users WHERE user_role = 3");
    
    echo "Admin accounts:\n";
    foreach ($admins as $admin) {
        echo "  - {$admin['user_name']} ({$admin['user_email']})\n";
    }
    
    echo "\nPassword for all admins: password123\n";
} else {
    echo "❌ Failed to reset passwords\n";
}
?>
