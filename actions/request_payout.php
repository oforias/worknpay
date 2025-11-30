<?php
/**
 * Request Payout Action
 * Allows workers to request withdrawal of their available balance
 */

header('Content-Type: application/json');

require_once '../settings/core.php';
require_once '../controllers/payout_controller.php';
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

if (!$input || !isset($input['amount']) || !isset($input['payout_type'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Amount and payout type are required'
    ]);
    exit();
}

$worker_id = get_user_id();
$amount = floatval($input['amount']);
$payout_type = trim($input['payout_type']);
$payout_account_id = isset($input['account_id']) ? intval($input['account_id']) : null;

// Validate payout type
if (!in_array($payout_type, ['instant', 'next_day'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid payout type'
    ]);
    exit();
}

// Validate minimum amount
if ($amount < 50) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Minimum withdrawal amount is GH₵50'
    ]);
    exit();
}

// Get worker's available balance
$db = new db_connection();
$balance_query = "SELECT available_balance FROM worker_profiles WHERE user_id = $worker_id";
$balance_result = $db->db_fetch_one($balance_query);

if (!$balance_result) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Worker profile not found'
    ]);
    exit();
}

$available_balance = floatval($balance_result['available_balance']);

// Validate amount doesn't exceed balance
if ($amount > $available_balance) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Insufficient balance. Available: GH₵' . number_format($available_balance, 2)
    ]);
    exit();
}

// Get or validate payout account
$payout_account_class = new payout_account_class();

if ($payout_account_id) {
    // Verify account belongs to worker
    $account = $payout_account_class->get_account_by_id($payout_account_id);
    if (!$account || $account['worker_id'] != $worker_id) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid payout account'
        ]);
        exit();
    }
} else {
    // Get default account
    $account = $payout_account_class->get_default_account($worker_id);
    if (!$account) {
        echo json_encode([
            'status' => 'error',
            'message' => 'No payout account found. Please add a payout account first.'
        ]);
        exit();
    }
    $payout_account_id = $account['account_id'];
}

try {
    // Calculate fee
    $fee = ($payout_type === 'instant') ? ($amount * 0.02) : 0;
    $net_amount = $amount - $fee;
    
    // Start transaction
    $db->db_query("START TRANSACTION");
    
    // Deduct from available balance
    $deduct_query = "UPDATE worker_profiles 
                     SET available_balance = available_balance - $amount 
                     WHERE user_id = $worker_id AND available_balance >= $amount";
    
    if (!$db->db_query($deduct_query)) {
        $db->db_query("ROLLBACK");
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to deduct from balance'
        ]);
        exit();
    }
    
    // Create payout request
    if (request_payout_ctr($worker_id, $amount, $payout_type, $payout_account_id)) {
        // Get the payout ID
        $payout_id_query = "SELECT LAST_INSERT_ID() as payout_id";
        $payout_id_result = $db->db_fetch_one($payout_id_query);
        $payout_id = $payout_id_result['payout_id'];
        
        // Create transaction log
        $log_query = "INSERT INTO transaction_logs 
                      (user_id, transaction_type, amount, balance_before, balance_after, reference, description) 
                      VALUES ($worker_id, 'payout', $amount, $available_balance, " . ($available_balance - $amount) . ", 
                      'PAYOUT-$payout_id', 'Withdrawal request - $payout_type payout')";
        $db->db_query($log_query);
        
        // Commit transaction
        $db->db_query("COMMIT");
        
        // Log the request
        error_log("Worker #$worker_id requested payout: GH₵$amount ($payout_type) - Payout ID: $payout_id");
        
        // TODO: Send notification to admin
        
        $timing_message = ($payout_type === 'instant') 
            ? 'Your withdrawal will be processed immediately (usually within 1-2 hours).'
            : 'Your withdrawal will be processed within 24 hours during business hours.';
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Withdrawal request submitted successfully! ' . $timing_message,
            'payout_id' => $payout_id,
            'amount' => $amount,
            'fee' => $fee,
            'net_amount' => $net_amount,
            'new_balance' => $available_balance - $amount
        ]);
    } else {
        $db->db_query("ROLLBACK");
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to create payout request'
        ]);
    }
} catch (Exception $e) {
    $db->db_query("ROLLBACK");
    error_log("Error processing payout request: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred while processing your request'
    ]);
}
?>
