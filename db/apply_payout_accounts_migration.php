<?php
/**
 * Apply Payout Accounts Migration
 * Run this script to create the worker_payout_accounts table
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
$migration_file = __DIR__ . '/migrations/003_create_payout_accounts.sql';
$sql = file_get_contents($migration_file);

// Remove comments and split into individual statements
$statements = array_filter(
    array_map('trim', 
        preg_split('/;[\s]*\n/', $sql)
    ),
    function($stmt) {
        return !empty($stmt) && 
               strpos($stmt, '--') !== 0 && 
               strpos($stmt, 'USE') !== 0;
    }
);

echo "Applying migration: 003_create_payout_accounts.sql\n";
echo str_repeat('-', 60) . "\n\n";

$success = true;
$step = 1;

foreach ($statements as $statement) {
    $statement = trim($statement);
    if (empty($statement)) continue;
    
    // Skip SELECT statements used for verification in migration file
    if (stripos($statement, 'SELECT') === 0) {
        continue;
    }
    
    echo "Step " . $step . ": ";
    
    if ($conn->query($statement) === TRUE) {
        echo "✓ Success\n";
        $step++;
    } else {
        // Check if error is because table already exists
        if ($conn->errno == 1050) {
            echo "⚠ Table already exists (skipping)\n";
            $step++;
        } else {
            echo "✗ Failed\n";
            echo "Error: " . $conn->error . "\n";
            $success = false;
            break;
        }
    }
}

echo "\n" . str_repeat('-', 60) . "\n";

if ($success) {
    echo "Migration completed successfully!\n\n";
    
    // Verify the table was created
    echo "Verifying migration...\n";
    $verify_sql = "SHOW TABLES LIKE 'worker_payout_accounts'";
    
    $result = $conn->query($verify_sql);
    if ($result && $result->num_rows > 0) {
        echo "✓ Table 'worker_payout_accounts' exists\n\n";
        
        // Show table structure
        echo "Table structure:\n";
        $structure = $conn->query("DESCRIBE worker_payout_accounts");
        if ($structure) {
            while ($row = $structure->fetch_assoc()) {
                echo "  - " . $row['Field'] . " (" . $row['Type'] . ")\n";
            }
        }
    } else {
        echo "✗ Table 'worker_payout_accounts' not found\n";
    }
    
    // Check if disputes table was updated
    echo "\nVerifying disputes table updates...\n";
    $disputes_check = $conn->query("SHOW COLUMNS FROM disputes LIKE 'worker_response'");
    if ($disputes_check && $disputes_check->num_rows > 0) {
        echo "✓ Disputes table updated with worker_response fields\n";
    }
} else {
    echo "Migration failed. Please check the errors above.\n";
}

$conn->close();
?>
