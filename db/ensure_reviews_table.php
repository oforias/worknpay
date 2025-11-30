<?php
/**
 * Ensure Reviews Table Exists
 * Creates the reviews table if it doesn't exist
 */

require_once __DIR__ . '/../settings/db_class.php';

$db = new db_connection();

// Check if reviews table exists
$check_query = "SHOW TABLES LIKE 'reviews'";
$result = $db->db_fetch_one($check_query);

if (!$result) {
    echo "Reviews table does not exist. Creating it now...\n";
    
    // Read the SQL file
    $sql = file_get_contents(__DIR__ . '/create_reviews_table.sql');
    
    // Execute the SQL
    if ($db->db_query($sql)) {
        echo "✓ Reviews table created successfully!\n";
    } else {
        echo "✗ Failed to create reviews table.\n";
        echo "Error: " . mysqli_error($db->db_connect()) . "\n";
    }
} else {
    echo "✓ Reviews table already exists.\n";
}

// Verify table structure
$describe_query = "DESCRIBE reviews";
$structure = $db->db_fetch_all($describe_query);

if ($structure) {
    echo "\nReviews table structure:\n";
    echo "------------------------\n";
    foreach ($structure as $column) {
        echo $column['Field'] . " - " . $column['Type'] . "\n";
    }
} else {
    echo "\n✗ Could not verify table structure.\n";
}
?>
