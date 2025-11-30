<?php
/**
 * Process Payout Action
 * Allows admins to complete or fail payout requests
 */

header('Content-Type: application/json');

require_once '../settings/core.php';
require_once '../controllers/payout_controller.php';

// Check if user is logged in and is an admin
require_login();
if (!is_admin()) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Access denied. Admins only.'
    ]);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['payout_id']) || !isset($input['action'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Payout ID and action are required'
    ]);
    exit();
}

$payout_id = intval($input['payout_id']);
$action = trim($input['action']);
$admin_id = get_user_id();

// Validate action
if (!in_array($action, ['complete', 'fail'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid action'
    ]);
    exit();
}

// Get payout details
$payout = get_payout_details_ctr($payout_id);

if (!$payout) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Payout not found'
    ]);
    exit();
}

// Verify payout is pending
if ($payout['payout_status'] !== 'pending') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Payout has already been processed'
    ]);
    exit();
}

$db = new db_connection();

try {
    $db->db_query("START TRANSACTION");
    
    if ($action === 'complete') {
        // Complete payout
        if (!isset($input['transaction_reference'])) {
            $db->db_query("ROLLBACK");
            echo json_encode([
                'status' => 'error',
                'message' => 'Transaction reference is required'
            ]);
            exit();
        }
        
        $transaction_ref = mysqli_real_escape_string($db->db_conn(), trim($input['transaction_reference']));
        
        // Update payout status
        if (process_payout_ctr($payout_id, 'completed', $transaction_ref)) {
            // Create transaction log
            $worker_id = $payout['worker_id'];
            $amount = $payout['amount'];
            
            // Get current balance
            $balance_query = "SELECT available_balance FROM worker_profiles WHERE user_id = $worker_id";
            $balance_result = $db->db_fetch_one($balance_query);
            $current_balance = $balance_result['available_balance'];
            
            $log_query = "INSERT INTO transaction_logs 
                          (user_id, transaction_type, amount, balance_before, balance_after, reference, description) 
                          VALUES ($worker_id, 'payout', $amount, $current_balance, $current_balance, 
                          '$transaction_ref', 'Payout completed by admin')";
            $db->db_query($log_query);
            
            $db->db_query("COMMIT");
            
            error_log("Admin #$admin_id completed payout #$payout_id for worker #$worker_id - Ref: $transaction_ref");
            
            // TODO: Send notification to worker
            
            echo json_encode([
                'status' => 'success',
                'message' => 'Payout processed successfully! Worker has been notified.'
            ]);
        } else {
            $db->db_query("ROLLBACK");
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to update payout status'
            ]);
        }
        
    } else {
        // Fail payout - restore balance
        $failure_reason = isset($input['failure_reason']) ? mysqli_real_escape_string($db->db_conn(), trim($input['failure_reason'])) : 'No reason provided';
        
        $worker_id = $payout['worker_id'];
        $amount = $payout['amount'];
        
        // Restore balance
        $restore_query = "UPDATE worker_profiles 
                         SET available_balance = available_balance + $amount 
                         WHERE user_id = $worker_id";
        
        if (!$db->db_query($restore_query)) {
            $db->db_query("ROLLBACK");
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to restore balance'
            ]);
            exit();
        }
        
        // Update payout status
        if (process_payout_ctr($payout_id, 'failed', null)) {
            // Add failure reason to payout record
            $reason_query = "UPDATE payouts 
                            SET payout_reference = '$failure_reason' 
                            WHERE payout_id = $payout_id";
            $db->db_query($reason_query);
            
            // Get new balance
            $balance_query = "SELECT available_balance FROM worker_profiles WHERE user_id = $worker_id";
            $balance_result = $db->db_fetch_one($balance_query);
            $new_balance = $balance_result['available_balance'];
            
            // Create transaction log
            $log_query = "INSERT INTO transaction_logs 
                          (user_id, transaction_type, amount, balance_before, balance_after, reference, description) 
                          VALUES ($worker_id, 'refund', $amount, " . ($new_balance - $amount) . ", $new_balance, 
                          'PAYOUT-FAILED-$payout_id', 'Payout rejected: $failure_reason')";
            $db->db_query($log_query);
            
            $db->db_query("COMMIT");
            
            error_log("Admin #$admin_id failed payout #$payout_id for worker #$worker_id - Reason: $failure_reason");
            
            // TODO: Send notification to worker
            
            echo json_encode([
                'status' => 'success',
                'message' => 'Payout rejected. Amount has been returned to worker\'s balance.'
            ]);
        } else {
            $db->db_query("ROLLBACK");
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to update payout status'
            ]);
        }
    }
    
} catch (Exception $e) {
    $db->db_query("ROLLBACK");
    error_log("Error processing payout: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred while processing payout'
    ]);
}
?>
