<?php
/**
 * Set Default Payout Account Action
 * Allows workers to set their default payout account
 */

header('Content-Type: application/json');

require_once '../settings/core.php';
require_once '../classes/payout_account_class.php';

// Check if user is logged in and is a worker
require_login();
if (!is_worker()) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Access denied. Workers only.'
    ]);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['account_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Account ID is required'
    ]);
    exit();
}

$worker_id = get_user_id();
$account_id = intval($input['account_id']);

$payout_account = new payout_account_class();

// Verify account belongs to this worker
$account = $payout_account->get_account_by_id($account_id);

if (!$account || $account['worker_id'] != $worker_id) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Account not found or access denied'
    ]);
    exit();
}

try {
    // Set as default
    if ($payout_account->set_default_account($account_id, $worker_id)) {
        error_log("Worker #$worker_id set account #$account_id as default");
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Default account updated successfully'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to update default account'
        ]);
    }
} catch (Exception $e) {
    error_log("Error setting default account: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred while updating default account'
        ]);
}
?>
