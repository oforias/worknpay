<?php
/**
 * Apply Database Migration
 * Run this script to apply the service_id nullable migration
 */

require_once(__DIR__ . '/../settings/db_cred.php');

// Connect to database
$conn = new mysqli(SERVER, USERNAME, PASSWD, DATABASE);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Connected to database successfully.\n\n";

// Read migration file
$migration_file = __DIR__ . '/migrations/001_make_service_id_nullable.sql';
$sql = file_get_contents($migration_file);

// Remove comments and split into individual statements
$statements = array_filter(
    array_map('trim', 
        preg_split('/;[\s]*\n/', $sql)
    ),
    function($stmt) {
        return !empty($stmt) && 
               strpos($stmt, '--') !== 0 && 
               strpos($stmt, 'USE') !== 0 &&
               strpos($stmt, 'SELECT') !== 0;
    }
);

echo "Applying migration: 001_make_service_id_nullable.sql\n";
echo str_repeat('-', 60) . "\n\n";

$success = true;

foreach ($statements as $index => $statement) {
    $statement = trim($statement);
    if (empty($statement)) continue;
    
    echo "Step " . ($index + 1) . ": ";
    
    if ($conn->query($statement) === TRUE) {
        echo "✓ Success\n";
    } else {
        echo "✗ Failed\n";
        echo "Error: " . $conn->error . "\n";
        $success = false;
        break;
    }
}

echo "\n" . str_repeat('-', 60) . "\n";

if ($success) {
    echo "Migration completed successfully!\n\n";
    
    // Verify the change
    echo "Verifying migration...\n";
    $verify_sql = "SELECT COLUMN_NAME, IS_NULLABLE, COLUMN_TYPE 
                   FROM INFORMATION_SCHEMA.COLUMNS 
                   WHERE TABLE_SCHEMA = '" . DATABASE . "' 
                     AND TABLE_NAME = 'bookings' 
                     AND COLUMN_NAME = 'service_id'";
    
    $result = $conn->query($verify_sql);
    if ($result && $row = $result->fetch_assoc()) {
        echo "Column: " . $row['COLUMN_NAME'] . "\n";
        echo "Type: " . $row['COLUMN_TYPE'] . "\n";
        echo "Nullable: " . $row['IS_NULLABLE'] . "\n\n";
        
        if ($row['IS_NULLABLE'] === 'YES') {
            echo "✓ Verification passed: service_id is now nullable\n";
        } else {
            echo "✗ Verification failed: service_id is still NOT NULL\n";
        }
    }
} else {
    echo "Migration failed. Please check the errors above.\n";
}

$conn->close();
?>
