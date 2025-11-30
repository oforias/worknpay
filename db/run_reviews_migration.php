<?php
/**
 * Run Reviews Table Migration
 * Creates the reviews table in the database
 */

require_once __DIR__ . '/../settings/db_class.php';

echo "Creating reviews table...\n";

$db = new db_connection();

// Read SQL file
$sql = file_get_contents(__DIR__ . '/create_reviews_table.sql');

// Execute SQL
if ($db->db_query($sql)) {
    echo "✓ Reviews table created successfully!\n";
} else {
    echo "✗ Error creating reviews table\n";
    echo "Error: " . mysqli_error($db->db_connect()) . "\n";
}

echo "\nMigration complete!\n";
?>
